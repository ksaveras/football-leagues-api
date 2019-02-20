<?php

namespace App\Tests\Controller;

use App\Entity\League;
use App\Tests\AbstractApiTestCase;

/**
 * Class LeagueControllerTest.
 */
class LeagueControllerTest extends AbstractApiTestCase
{
    public function testIndex(): void
    {
        $jwtToken = $this->getToken();

        $this->client->request(
            'GET',
            '/api/leagues',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtToken,
            ]
        );

        $expected = \json_encode(
            [
                'leagues' => [
                    ['id' => 1, 'name' => 'League 1', 'teams' => [['id' => 1], ['id' => 2]]],
                    ['id' => 2, 'name' => 'League 2', 'teams' => [['id' => 2], ['id' => 3]]],
                ],
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals($expected, $response->getContent());
    }

    public function testCreate(): void
    {
        $jwtToken = $this->getToken();

        $newLeague = ['name' => 'Super League'];
        /** @var string $body */
        $body = \json_encode($newLeague);

        $this->client->request(
            'POST',
            '/api/leagues',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtToken,
            ],
            $body
        );

        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertTrue($response->headers->has('location'));

        $url = $response->headers->get('location');
        $this->assertRegExp('#/api/leagues/\d+#i', $url);
        preg_match('#/(?<leagueId>\d+)$#', $url, $match);

        /** @var League $leagueEntity */
        $leagueEntity = static::$container->get('doctrine.orm.entity_manager')
            ->getRepository(League::class)
            ->find($match['leagueId']);

        $this->assertInstanceOf(League::class, $leagueEntity);
        $this->assertEquals($newLeague['name'], $leagueEntity->getName());
        $this->assertCount(0, $leagueEntity->getTeams());
    }

    public function testShow(): void
    {
        $jwtToken = $this->getToken();

        $this->client->request(
            'GET',
            '/api/leagues/1',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtToken,
            ]
        );

        $expected = \json_encode(
            ['id' => 1, 'name' => 'League 1', 'teams' => [['id' => 1], ['id' => 2]]]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals($expected, $response->getContent());
    }

    public function testUpdate(): void
    {
        $jwtToken = $this->getToken();

        $updatedTeam = ['name' => 'Super League'];
        /** @var string $body */
        $body = \json_encode($updatedTeam);

        $this->client->request(
            'PUT',
            '/api/leagues/1',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtToken,
            ],
            $body
        );

        $response = $this->client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($response->headers->get('Content-Type'));

        /** @var League $leagueEntity */
        $leagueEntity = static::$container->get('doctrine.orm.entity_manager')
            ->getRepository(League::class)
            ->find(1);

        $this->assertInstanceOf(League::class, $leagueEntity);
        $this->assertEquals($updatedTeam['name'], $leagueEntity->getName());
        $this->assertCount(2, $leagueEntity->getTeams());
    }

    public function testDelete(): void
    {
        $jwtToken = $this->getToken();

        $this->client->request(
            'DELETE',
            '/api/leagues/1',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtToken,
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($response->headers->get('Content-Type'));

        $leagueEntity = static::$container->get('doctrine.orm.entity_manager')
            ->getRepository(League::class)
            ->find(1);

        $this->assertNull($leagueEntity);
    }

    public function testListTeams(): void
    {
        $jwtToken = $this->getToken();

        $this->client->request(
            'GET',
            '/api/leagues/1/teams',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtToken,
            ]
        );

        $expected = \json_encode(
            [
                'teams' => [
                    ['id' => 1, 'name' => 'Team 1', 'strip' => 'strip-1'],
                    ['id' => 2, 'name' => 'Team 2', 'strip' => 'strip-2'],
                ],
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals($expected, $response->getContent());
    }

    public function testAddTeam(): void
    {
        $jwtToken = $this->getToken();

        $this->client->request(
            'POST',
            '/api/leagues/1/teams/3',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtToken,
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($response->headers->get('Content-Type'));

        /** @var League $leagueEntity */
        $leagueEntity = static::$container->get('doctrine.orm.entity_manager')
            ->getRepository(League::class)
            ->createQueryBuilder('l')
            ->select('t', 'l')
            ->innerJoin('l.teams', 't')
            ->where('l.id = :leagueId')
            ->setParameter('leagueId', 1)
            ->getQuery()
            ->getOneOrNullResult();

        $teamIds = $leagueEntity->getTeams()->map(
            function (\App\Entity\Team $team) {
                return $team->getId();
            }
        );

        $this->assertContains(3, $teamIds->toArray());
    }

    public function testRemoveTeam(): void
    {
        $jwtToken = $this->getToken();

        $this->client->request(
            'DELETE',
            '/api/leagues/1/teams/1',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtToken,
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($response->headers->get('Content-Type'));

        /** @var League $leagueEntity */
        $leagueEntity = static::$container->get('doctrine.orm.entity_manager')
            ->getRepository(League::class)
            ->createQueryBuilder('l')
            ->select('t', 'l')
            ->innerJoin('l.teams', 't')
            ->where('l.id = :leagueId')
            ->setParameter('leagueId', 1)
            ->getQuery()
            ->getOneOrNullResult();

        $teamIds = $leagueEntity->getTeams()->map(
            function (\App\Entity\Team $team) {
                return $team->getId();
            }
        );

        $this->assertNotContains(1, $teamIds->toArray());
    }
}

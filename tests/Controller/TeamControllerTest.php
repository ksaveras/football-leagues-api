<?php

namespace App\Tests\Controller;

use App\Entity\Team;
use App\Tests\AbstractApiTestCase;

/**
 * Class TeamControllerTest.
 */
class TeamControllerTest extends AbstractApiTestCase
{
    public function testIndex(): void
    {
        $jwtToken = $this->getToken();

        $this->client->request(
            'GET',
            '/api/teams',
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
                    ['id' => 1, 'name' => 'Team 1', 'strip' => 'strip-1', 'leagues' => [['id' => 1]]],
                    ['id' => 2, 'name' => 'Team 2', 'strip' => 'strip-2', 'leagues' => [['id' => 1], ['id' => 2]]],
                    ['id' => 3, 'name' => 'Team 3', 'strip' => 'strip-3', 'leagues' => [['id' => 2]]],
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

        $newTeam = ['name' => 'Golden Team', 'strip' => 'yellow'];
        /** @var string $body */
        $body = \json_encode($newTeam);

        $this->client->request(
            'POST',
            '/api/teams',
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
        $this->assertRegExp('#/api/teams/\d+#i', $url);
        preg_match('#/(?<teamId>\d+)$#', $url, $match);

        /** @var Team $teamEntity */
        $teamEntity = static::$container->get('doctrine.orm.entity_manager')
            ->getRepository(Team::class)
            ->find($match['teamId']);

        $this->assertInstanceOf(Team::class, $teamEntity);
        $this->assertEquals($newTeam['name'], $teamEntity->getName());
        $this->assertEquals($newTeam['strip'], $teamEntity->getStrip());
        $this->assertCount(0, $teamEntity->getLeagues());
    }

    public function testShow(): void
    {
        $jwtToken = $this->getToken();

        $this->client->request(
            'GET',
            '/api/teams/1',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtToken,
            ]
        );

        $expected = \json_encode(
            ['id' => 1, 'name' => 'Team 1', 'strip' => 'strip-1', 'leagues' => [['id' => 1]]]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals($expected, $response->getContent());
    }

    public function testUpdate(): void
    {
        $jwtToken = $this->getToken();

        $updatedTeam = ['name' => 'Golden Team', 'strip' => 'yellow'];
        /** @var string $body */
        $body = \json_encode($updatedTeam);

        $this->client->request(
            'PUT',
            '/api/teams/1',
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

        /** @var Team $teamEntity */
        $teamEntity = static::$container->get('doctrine.orm.entity_manager')
            ->getRepository(Team::class)
            ->find(1);

        $this->assertInstanceOf(Team::class, $teamEntity);
        $this->assertEquals($updatedTeam['name'], $teamEntity->getName());
        $this->assertEquals($updatedTeam['strip'], $teamEntity->getStrip());
        $this->assertCount(1, $teamEntity->getLeagues());
    }

    public function testDelete(): void
    {
        $jwtToken = $this->getToken();

        $this->client->request(
            'DELETE',
            '/api/teams/1',
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

        $teamEntity = static::$container->get('doctrine.orm.entity_manager')
            ->getRepository(Team::class)
            ->find(1);

        $this->assertNull($teamEntity);
    }
}

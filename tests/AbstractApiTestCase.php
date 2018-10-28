<?php

namespace App\Tests;

use App\Security\TokenManager;
use Doctrine\ORM\Tools\SchemaTool;
use Lcobucci\JWT\Token;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class AbstractApiTestCase.
 */
abstract class AbstractApiTestCase extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @return Token
     */
    public function getToken(): Token
    {
        return static::$container->get(TokenManager::class)->create('demo');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->seedDatabase(static::$kernel);
    }

    /**
     * @param KernelInterface $kernel
     */
    protected function seedDatabase(KernelInterface $kernel): void
    {
        if ('test' !== $kernel->getEnvironment()) {
            throw new \LogicException('Method can be executed in the test environment');
        }

        $entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->updateSchema($metadatas);

        $team1 = (new \App\Entity\Team())->setName('Team 1')->setStrip('strip-1');
        $team2 = (new \App\Entity\Team())->setName('Team 2')->setStrip('strip-2');
        $team3 = (new \App\Entity\Team())->setName('Team 3')->setStrip('strip-3');
        $league1 = (new \App\Entity\League())->setName('League 1')->addTeam($team1)->addTeam($team2);
        $league2 = (new \App\Entity\League())->setName('League 2')->addTeam($team2)->addTeam($team3);

        $entityManager->persist($team1);
        $entityManager->persist($team2);
        $entityManager->persist($team3);
        $entityManager->persist($league1);
        $entityManager->persist($league2);

        $entityManager->flush();
    }
}

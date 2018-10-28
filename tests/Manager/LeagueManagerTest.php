<?php

namespace App\Tests\Manager;

use App\Entity\League;
use App\Entity\Team;
use App\Manager\LeagueManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class LeagueManagerTest.
 */
class LeagueManagerTest extends TestCase
{
    public function testGetAll(): void
    {
        $league = new League();

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([$league]);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(League::class)
            ->willReturn($repository);

        $validator = $this->createMock(ValidatorInterface::class);

        $manager = new LeagueManager($entityManager, $validator);

        $leagues = $manager->getAll();
        $this->assertCount(1, $leagues);
        $this->assertEquals($league, $leagues[0]);
    }

    public function testCreateValid(): void
    {
        $league = (new League())
            ->setName('Demo League');

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($league);
        $entityManager->expects($this->once())
            ->method('flush');

        $violationList = $this->createMock(ConstraintViolationListInterface::class);
        $violationList->method('count')
            ->willReturn(0);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($league)
            ->willReturn($violationList);

        $manager = new LeagueManager($entityManager, $validator);

        $manager->create($league);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage League entity is invalid
     */
    public function testCreateInvalid(): void
    {
        $league = new League();

        $entityManager = $this->createMock(EntityManager::class);

        $violationList = $this->createMock(ConstraintViolationListInterface::class);
        $violationList->method('count')
            ->willReturn(1);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($league)
            ->willReturn($violationList);

        $manager = new LeagueManager($entityManager, $validator);

        $manager->create($league);
    }

    public function testUpdateValid(): void
    {
        $league = (new League())
            ->setName('Demo League');

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('flush');

        $violationList = $this->createMock(ConstraintViolationListInterface::class);
        $violationList->method('count')
            ->willReturn(0);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($league)
            ->willReturn($violationList);

        $manager = new LeagueManager($entityManager, $validator);

        $manager->update($league);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage League entity is invalid
     */
    public function testUpdateInvalid(): void
    {
        $league = new League();

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('detach')
            ->with($league);

        $violationList = $this->createMock(ConstraintViolationListInterface::class);
        $violationList->method('count')
            ->willReturn(1);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($league)
            ->willReturn($violationList);

        $manager = new LeagueManager($entityManager, $validator);

        $manager->update($league);
    }

    public function testDelete(): void
    {
        $league = new League();

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($league);
        $entityManager->expects($this->once())
            ->method('flush');

        $validator = $this->createMock(ValidatorInterface::class);

        $manager = new LeagueManager($entityManager, $validator);

        $manager->delete($league);
    }

    public function testAddTeam(): void
    {
        $league = new League();
        $teamId = 1;
        $team = new Team();

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getReference')
            ->with(Team::class, 1)
            ->willReturn($team);
        $entityManager->expects($this->once())
            ->method('flush');

        $validator = $this->createMock(ValidatorInterface::class);

        $manager = new LeagueManager($entityManager, $validator);

        $manager->addTeam($league, $teamId);

        $this->assertCount(1, $league->getTeams());
        $this->assertSame($team, $league->getTeams()->first());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Team is already in that league
     */
    public function testAddTeamExisting(): void
    {
        $league = new League();
        $teamId = 1;
        $team = new Team();

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getReference')
            ->with(Team::class, 1)
            ->willReturn($team);
        $entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException(
                $this->createMock(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class)
            );

        $validator = $this->createMock(ValidatorInterface::class);

        $manager = new LeagueManager($entityManager, $validator);

        $manager->addTeam($league, $teamId);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Team not found
     */
    public function testAddNonexistentTeam(): void
    {
        $league = new League();
        $teamId = 1;
        $team = new Team();

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getReference')
            ->with(Team::class, 1)
            ->willReturn($team);
        $entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException(
                $this->createMock(\Doctrine\ORM\EntityNotFoundException::class)
            );

        $validator = $this->createMock(ValidatorInterface::class);

        $manager = new LeagueManager($entityManager, $validator);

        $manager->addTeam($league, $teamId);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Error adding team to league
     */
    public function testAddTeamError(): void
    {
        $league = new League();
        $teamId = 1;
        $team = new Team();

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getReference')
            ->with(Team::class, 1)
            ->willReturn($team);
        $entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException(
                $this->createMock(\Exception::class)
            );

        $validator = $this->createMock(ValidatorInterface::class);

        $manager = new LeagueManager($entityManager, $validator);

        $manager->addTeam($league, $teamId);
    }

    public function testRemoveTeam(): void
    {
        $team = new Team();
        $league = new League();
        $league->addTeam($team);
        $teamId = 1;

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getReference')
            ->with(Team::class, 1)
            ->willReturn($team);
        $entityManager->expects($this->once())
            ->method('flush');

        $validator = $this->createMock(ValidatorInterface::class);

        $manager = new LeagueManager($entityManager, $validator);

        $manager->removeTeam($league, $teamId);

        $this->assertCount(0, $league->getTeams());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Team not found
     */
    public function testRemoveNonexistentTeam(): void
    {
        $team = new Team();
        $league = new League();
        $league->addTeam($team);
        $teamId = 1;

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getReference')
            ->with(Team::class, 1)
            ->willReturn($team);
        $entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException(
                $this->createMock(\Doctrine\ORM\EntityNotFoundException::class)
            );

        $validator = $this->createMock(ValidatorInterface::class);

        $manager = new LeagueManager($entityManager, $validator);

        $manager->removeTeam($league, $teamId);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Error removing team from league
     */
    public function testRemoveTeamError(): void
    {
        $team = new Team();
        $league = new League();
        $league->addTeam($team);
        $teamId = 1;

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getReference')
            ->with(Team::class, 1)
            ->willReturn($team);
        $entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException(
                $this->createMock(\Exception::class)
            );

        $validator = $this->createMock(ValidatorInterface::class);

        $manager = new LeagueManager($entityManager, $validator);

        $manager->removeTeam($league, $teamId);
    }
}

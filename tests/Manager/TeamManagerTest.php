<?php

namespace App\Tests\Manager;

use App\Entity\Team;
use App\Manager\TeamManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class TeamManagerTest.
 */
class TeamManagerTest extends TestCase
{
    public function testGetAll(): void
    {
        $team = new Team();

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([$team]);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Team::class)
            ->willReturn($repository);

        $validator = $this->createMock(ValidatorInterface::class);

        $manager = new TeamManager($entityManager, $validator);

        $teams = $manager->getAll();
        $this->assertCount(1, $teams);
        $this->assertEquals($team, $teams[0]);
    }

    public function testCreateValid(): void
    {
        $team = (new Team())
            ->setName('Demo Team')
            ->setStrip('White/Yellow');

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($team);
        $entityManager->expects($this->once())
            ->method('flush');

        $violationList = $this->createMock(ConstraintViolationListInterface::class);
        $violationList->method('count')
            ->willReturn(0);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($team)
            ->willReturn($violationList);

        $manager = new TeamManager($entityManager, $validator);

        $manager->create($team);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Team entity is invalid
     */
    public function testCreateInvalid(): void
    {
        $team = new Team();

        $entityManager = $this->createMock(EntityManager::class);

        $violationList = $this->createMock(ConstraintViolationListInterface::class);
        $violationList->method('count')
            ->willReturn(1);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($team)
            ->willReturn($violationList);

        $manager = new TeamManager($entityManager, $validator);

        $manager->create($team);
    }

    public function testUpdateValid(): void
    {
        $team = (new Team())
            ->setName('Demo Team')
            ->setStrip('White/Yellow');

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('flush');

        $violationList = $this->createMock(ConstraintViolationListInterface::class);
        $violationList->method('count')
            ->willReturn(0);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($team)
            ->willReturn($violationList);

        $manager = new TeamManager($entityManager, $validator);

        $manager->update($team);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Team entity is invalid
     */
    public function testUpdateInvalid(): void
    {
        $team = new Team();

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('detach')
            ->with($team);

        $violationList = $this->createMock(ConstraintViolationListInterface::class);
        $violationList->method('count')
            ->willReturn(1);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($team)
            ->willReturn($violationList);

        $manager = new TeamManager($entityManager, $validator);

        $manager->update($team);
    }

    public function testDelete(): void
    {
        $team = new Team();

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($team);
        $entityManager->expects($this->once())
            ->method('flush');

        $validator = $this->createMock(ValidatorInterface::class);

        $manager = new TeamManager($entityManager, $validator);

        $manager->delete($team);
    }
}

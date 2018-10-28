<?php

namespace App\Manager;

use App\Entity\League;
use App\Entity\Team;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class LeagueManager.
 */
class LeagueManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * LeagueManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface     $validator
     */
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * @return League[]|array|object[]
     */
    public function getAll(): array
    {
        return $this->entityManager
            ->getRepository(League::class)
            ->findAll();
    }

    /**
     * @param League $league
     */
    public function create(League $league): void
    {
        $violations = $this->validator->validate($league);
        if ($violations->count() > 0) {
            $this->entityManager->detach($league);
            throw new \InvalidArgumentException('League entity is invalid');
        }

        $this->entityManager->persist($league);
        $this->entityManager->flush($league);
    }

    /**
     * @param League $league
     */
    public function update(League $league): void
    {
        $violations = $this->validator->validate($league);
        if ($violations->count() > 0) {
            $this->entityManager->detach($league);
            throw new \InvalidArgumentException('League entity is invalid');
        }

        $this->entityManager->flush($league);
    }

    /**
     * @param League $league
     */
    public function delete(League $league): void
    {
        $this->entityManager->remove($league);
        $this->entityManager->flush($league);
    }

    /**
     * @param League $league
     * @param int    $teamId
     */
    public function addTeam(League $league, int $teamId): void
    {
        try {
            $league->addTeam($this->entityManager->getReference(Team::class, $teamId));
            $this->entityManager->flush($league);
        } catch (UniqueConstraintViolationException $exception) {
            throw new \InvalidArgumentException('Team is already in that league');
        } catch (EntityNotFoundException $exception) {
            throw new \InvalidArgumentException('Team not found');
        } catch (\Exception $exception) {
            throw new \InvalidArgumentException('Error adding team to league');
        }
    }

    /**
     * @param League $league
     * @param int    $teamId
     */
    public function removeTeam(League $league, int $teamId): void
    {
        try {
            $league->removeTeam($this->entityManager->getReference(Team::class, $teamId));
            $this->entityManager->flush($league);
        } catch (EntityNotFoundException $exception) {
            throw new \InvalidArgumentException('Team not found');
        } catch (\Exception $exception) {
            throw new \InvalidArgumentException('Error removing team from league');
        }
    }
}

<?php

namespace App\Manager;

use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class TeamManager.
 */
class TeamManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * TeamManager constructor.
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
     * @return Team[]|array|object[]
     */
    public function getAll(): array
    {
        return $this->entityManager
            ->getRepository(Team::class)
            ->findAll();
    }

    /**
     * @param Team $team
     */
    public function create(Team $team): void
    {
        $violations = $this->validator->validate($team);
        if ($violations->count() > 0) {
            $this->entityManager->detach($team);
            throw new \InvalidArgumentException('Team entity is invalid');
        }

        $this->entityManager->persist($team);
        $this->entityManager->flush();
    }

    /**
     * @param Team $team
     */
    public function update(Team $team): void
    {
        $violations = $this->validator->validate($team);
        if ($violations->count() > 0) {
            $this->entityManager->detach($team);
            throw new \InvalidArgumentException('Team entity is invalid');
        }

        $this->entityManager->flush();
    }

    /**
     * @param Team $team
     */
    public function delete(Team $team): void
    {
        $this->entityManager->remove($team);
        $this->entityManager->flush();
    }
}

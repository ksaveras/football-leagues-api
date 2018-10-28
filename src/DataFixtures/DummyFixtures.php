<?php

namespace App\DataFixtures;

use App\Entity\League;
use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class DummyFixtures.
 *
 * @codeCoverageIgnore
 */
class DummyFixtures extends Fixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $teams = [];
        foreach (range(1, 20) as $index) {
            $team = (new Team())
                ->setName('Team ' . $index)
                ->setStrip('strip ' . random_int(1, 100));

            $teams[] = $team;
            $manager->persist($team);
        }

        foreach (range(1, 10) as $index) {
            $league = (new League())
                ->setName('League ' . $index);

            $teamIds = array_values(
                array_unique(
                    array_map(
                        function ($item) {
                            return random_int(0, 19);
                        },
                        array_fill(0, random_int(1, 20), false)
                    )
                )
            );

            foreach ($teamIds as $teamId) {
                $league->addTeam($teams[$teamId]);
            }

            $manager->persist($league);
        }

        $manager->flush();
    }
}

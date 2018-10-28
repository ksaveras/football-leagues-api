<?php

namespace App\Controller;

use App\Entity\League;
use App\Manager\LeagueManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class LeagueController.
 */
class LeagueController extends AbstractController
{
    /**
     * @var LeagueManager
     */
    private $manager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * LeagueController constructor.
     *
     * @param LeagueManager       $manager
     * @param SerializerInterface $serializer
     */
    public function __construct(LeagueManager $manager, SerializerInterface $serializer)
    {
        $this->manager = $manager;
        $this->serializer = $serializer;
    }

    /**
     * @return Response
     */
    public function index(): Response
    {
        $leagues = $this->manager->getAll();
        $data = ['leagues' => $leagues];

        return $this->json($data, 200, [], ['groups' => ['league_list']]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        try {
            /** @var League $league */
            $league = $this->serializer->deserialize(
                $request->getContent(),
                League::class,
                $request->getContentType(),
                [
                    'groups' => ['league_edit'],
                ]
            );

            $this->manager->create($league);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        return new Response(
            '', 201,
            [
                'Location' => $this->generateUrl(
                    'leagues_show',
                    ['id' => $league->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]
        );
    }

    /**
     * @param League $league
     *
     * @return Response
     */
    public function show(League $league): Response
    {
        return $this->json($league, 200, [], ['groups' => ['league_show']]);
    }

    /**
     * @param Request $request
     * @param League  $league
     *
     * @return Response
     */
    public function update(Request $request, League $league): Response
    {
        try {
            $this->serializer->deserialize(
                $request->getContent(),
                League::class,
                $request->getContentType(),
                [
                    'groups' => ['league_edit'],
                    'object_to_populate' => $league,
                ]
            );

            $this->manager->update($league);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        return new Response('', 204);
    }

    /**
     * @param League $league
     *
     * @return Response
     */
    public function delete(League $league): Response
    {
        try {
            $this->manager->delete($league);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        return new Response('', 204);
    }

    /**
     * @param League $league
     *
     * @return Response
     */
    public function listTeams(League $league): Response
    {
        return $this->json(['teams' => $league->getTeams()], 200, [], ['groups' => ['league_teams']]);
    }

    /**
     * @param League $league
     * @param int    $teamId
     *
     * @return Response
     */
    public function addTeam(League $league, $teamId): Response
    {
        try {
            $this->manager->addTeam($league, $teamId);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        return new Response('', 204);
    }

    /**
     * @param League $league
     * @param int    $teamId
     *
     * @return Response
     */
    public function removeTeam(League $league, $teamId): Response
    {
        try {
            $this->manager->removeTeam($league, $teamId);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        return new Response('', 204);
    }
}

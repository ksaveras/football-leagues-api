<?php

namespace App\Controller;

use App\Entity\Team;
use App\Manager\TeamManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class TeamController.
 */
class TeamController extends AbstractController
{
    /**
     * @var TeamManager
     */
    private $manager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * TeamController constructor.
     *
     * @param TeamManager         $manager
     * @param SerializerInterface $serializer
     */
    public function __construct(TeamManager $manager, SerializerInterface $serializer)
    {
        $this->manager = $manager;
        $this->serializer = $serializer;
    }

    /**
     * @return Response
     */
    public function index(): Response
    {
        $teams = $this->manager->getAll();
        $data = ['teams' => $teams];

        return $this->json($data, 200, [], ['groups' => ['team_list']]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        try {
            /** @var Team $team */
            $team = $this->serializer->deserialize(
                $request->getContent(),
                Team::class,
                $request->getContentType(),
                [
                    'groups' => ['team_edit'],
                ]
            );

            $this->manager->create($team);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        return new Response(
            '',
            201,
            [
                'Location' => $this->generateUrl(
                    'teams_show',
                    ['id' => $team->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]
        );
    }

    /**
     * @param Team $team
     *
     * @return Response
     */
    public function show(Team $team): Response
    {
        return $this->json($team, 200, [], ['groups' => ['team_show']]);
    }

    /**
     * @param Request $request
     * @param Team    $team
     *
     * @return Response
     */
    public function update(Request $request, Team $team): Response
    {
        try {
            $this->serializer->deserialize(
                $request->getContent(),
                Team::class,
                $request->getContentType(),
                [
                    'groups' => ['team_edit'],
                    'object_to_populate' => $team,
                ]
            );

            $this->manager->update($team);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        return new Response('', 204);
    }

    /**
     * @param Team $team
     *
     * @return Response
     */
    public function delete(Team $team): Response
    {
        try {
            $this->manager->delete($team);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        return new Response('', 204);
    }
}

<?php

namespace App\Controller\API\V1;

use App\Entity\User;
use App\Entity\UserPoint;
use App\Repository\UserPointRepository;
use App\Repository\UserRepository;
use App\Service\AutoMapper;
use DateTime;
use Develia\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api/v1/users", name="users_")
 */
class UserController extends AbstractController {

    private AutoMapper $autoMapper;
    private Security $security;
    private UserRepository $userRepository;
    private UserPointRepository $userPointRepository;

    public function __construct(UserRepository $userRepository, UserPointRepository $userPointRepository, AutoMapper $autoMapper, Security $security) {

        $this->userRepository = $userRepository;
        $this->userPointRepository = $userPointRepository;
        $this->autoMapper = $autoMapper;
        $this->security = $security;
    }

    /**
     * @Route("/{id}/points", name="get_points", methods={"GET"})
     */
    public function getPoints($id, Request $request) {

        if ($id == "me")
            $id = $this->userRepository->getCurrentUser()->getId();
        else if ($this->security->isGranted('ROLE_TECHNICIAN_SUPERVISOR'))
            return new JsonResponse(null, 403);


        $request->getQuery()->tryGetDate("fromDate", $fromDate);
        $request->getQuery()->tryGetDate("toDate", $toDate);


        $mapMultipleToDto = $this->autoMapper->mapMultipleToDto($this->userPointRepository->getPoints($id, $fromDate, $toDate));

        return $this->json($mapMultipleToDto);
    }

    /**
     * @Route("/{id}/points", name="add_points", methods={"POST"})
     */
    public function addPoints($id, Request $request) {

        if (!$this->security->isGranted('ROLE_TECHNICIAN_SUPERVISOR'))
            return new JsonResponse(null, 403);

        if ($request->getForm()->tryGetFloat("points", $points) &&
            $request->getForm()->tryGetString("reason", $reason)) {
            $userPoints = new UserPoint();
            $userPoints->setOwner($this->userRepository->find($id));
            $userPoints->setPoints($points);
            $userPoints->setReason($reason);
            if ($request->getForm()->tryGetDate("dateandtime", $dateandtime, "Y-m-d\TH:i")) {
                $userPoints->setDate($dateandtime);
            } else {
                $userPoints->setDate(new DateTime());
            }
            $this->userRepository->persist($userPoints, true);

            return new JsonResponse($this->autoMapper->mapToDto($userPoints));
        }

        return new JsonResponse(null, 400);

    }

    /**
     * @Route("/",  methods={"GET"})
     */
    public function getUsers(): Response {

        $queryBuilder = $this->userRepository->createQueryBuilder('u');

        $users = $queryBuilder->getQuery()->getResult();

        $output = from($users)
            ->orderBy(fn(User $x) => $x->getFirstName())
            ->map(fn($x) => [
                "id"        => $x->getId(),
                "firstName" => $x->getFirstName(),
                "lastName"  => $x->getLastName()
            ])
            ->toArray();

        return $this->json($output);
    }
}
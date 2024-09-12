<?php

namespace App\Controller;

use App\Repository\UserPointRepository;
use App\Repository\UserRepository;
use App\Service\AutoMapper;
use Develia\Date;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/user-points")
 */
class UserPointsController extends AbstractController {

    private Security $security;
    private UserRepository $userRepository;
    private UserPointRepository $userPointRepository;
    private AutoMapper $autoMapper;

    public function __construct(Security $security, UserRepository $userRepository, UserPointRepository $userPointRepository, AutoMapper $autoMapper) {

        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->userPointRepository = $userPointRepository;
        $this->autoMapper = $autoMapper;
    }

    /**
     * @Route("/")
     */
    public function index(Request $request): Response {

        $technicianId = $request->request->get("technician");

        $fromDate = null;
        if ($fromDateStr = $request->request->get("fromDate")) {
            $fromDate = Date::startOfDay(\DateTime::createFromFormat('Y-m-d', $fromDateStr));
        }

        $toDate = null;
        if ($toDateStr = $request->request->get("toDate")) {
            $toDate = Date::endOfDay(\DateTime::createFromFormat('Y-m-d', $toDateStr));
        }

        if ($this->security->isGranted("ROLE_TECHNICIAN_SUPERVISOR"))
            $technicians = $this->userRepository->findAll();
        else
            $technicians = [$this->userRepository->getCurrentUser()];


        if (!$this->security->isGranted('ROLE_TECHNICIAN_SUPERVISOR') && $technicianId != $this->userRepository->getCurrentUser()->getId())
            return new Response(null, 403);

        return $this->render('user-points/index.html.twig', [
            "technicians"  => $technicians,
            "technicianId" => $technicianId,
            "fromDate"     => $fromDateStr,
            "toDate"       => $toDateStr,
            "points"       => $this->autoMapper->mapMultipleToDto($this->userPointRepository->getPoints($technicianId, $fromDate, $toDate))
        ]);
    }
}
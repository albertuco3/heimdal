<?php

namespace App\Controller\API\V1;

use App\Repository\ArticleRepository;
use App\Repository\MovementRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api/v1/traceability", name="traceability_")
 */
class TraceabilityController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }



    /**
     * @Route("/by-serial-number/{serial}", name="by_serial_number", methods={"GET"})
     */
    public function getTraceabilityBySerialNumber(string $serial, ArticleRepository $articleRepository, MovementRepository $movementRepository): Response
    {
        $article = $articleRepository->findOneBy(['serialNumber' => $serial]);

        if (!$article) {
            return $this->json(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $movements = $movementRepository->findBy(['article' => $article]);

        // Aquí deberías transformar $movements a DTOs o arrays como prefieras antes de devolverlos

        return $this->json($movements);
    }

    /**
     * @Route("/by-worker/{id}", name="by_worker", methods={"GET"})
     */
    public function getTraceabilityByWorker(int $id, MovementRepository $movementRepository): Response
    {
        $movements = $movementRepository->findByWorker($id);

        // Aquí deberías transformar $movements a DTOs o arrays como prefieras antes de devolverlos

        return $this->json($movements);
    }

    // Implementa los métodos adicionales si es necesario
}
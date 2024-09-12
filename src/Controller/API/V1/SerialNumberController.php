<?php

namespace App\Controller\API\V1;


use App\Entity\SerialNumberProperties;
use App\Repository\SerialNumberPropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/serial-numbers", name="serial-numbers_")
 */
class SerialNumberController extends AbstractController {

    private SerialNumberPropertyRepository $serialNumberRepository;

    function __construct(SerialNumberPropertyRepository $serialNumberRepository) {

        $this->serialNumberRepository = $serialNumberRepository;
    }

    /**
     * @Route("/{serialNumber}", name="get", methods={"GET"})
     */
    function getSerialNumber(Request $request, string $serialNumber) {

        $entity = $this->serialNumberRepository->findOneBySerialNumber($serialNumber);
        $output = [
            "notes" => $entity ? $entity->getNotes() : "",
        ];
        return $this->json($output);
    }

    /**
     * @Route("/{serialNumber}", name="set", methods={"POST"})
     */
    function updateSerialNumber(Request $request, string $serialNumber) {

        $entity = $this->serialNumberRepository->findOneBySerialNumber($serialNumber);
        if (!$entity) {
            $entity = new SerialNumberProperties();
            $entity->setSerialNumber($serialNumber);
        }

        $notes = $request->request->get('notes');

        $entity->setNotes($notes);
        
        $this->serialNumberRepository->persist($entity);

        return new Response(null, 200);
    }
}



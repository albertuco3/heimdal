<?php

namespace App\Controller\API\V1;

use App\Entity\Article;
use App\Entity\Movement;
use App\Repository\ArticleRepository;
use App\Repository\JobTypeRepository;
use App\Repository\UserRepository;
use App\Service\BoxService;
use App\Service\XGestRestClient;
use App\Service\XGestService;
use Develia\Obj;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use function Doctrine\ORM\QueryBuilder;

/**
 * @Route("/api/v1/box", name="box_")
 */
class BoxController extends AbstractController {
    private Security $security;
    private UserRepository $userRepository;
    private JobTypeRepository $jobTypeRepository;
    private ArticleRepository $articleRepository;
    private EntityManagerInterface $em;
    private BoxService $boxService;
    private XGestService $xGestService;

    public function __construct(Security               $security,
                                JobTypeRepository      $jobTypeRepository,
                                UserRepository         $userRepository,
                                ArticleRepository      $articleRepository,
                                EntityManagerInterface $em,
                                BoxService             $boxService,
                                XGestService           $xGestService) {
        $this->security = $security;
        $this->jobTypeRepository = $jobTypeRepository;
        $this->userRepository = $userRepository;
        $this->articleRepository = $articleRepository;
        $this->em = $em;
        $this->boxService = $boxService;
        $this->xGestService = $xGestService;
    }

    /**
     * @Route("/xgest-importables", methods={"GET"})
     */
    public function getXGestImportables(\Develia\Request $request): Response {

        $request->getQuery()->tryGetDate("fromDate", $fromDate);
        $request->getQuery()->tryGetDate("toDate", $toDate);

        $request->getQuery()->tryGet("source", $source);
        $request->getQuery()->tryGet("company", $company);
        $request->getQuery()->tryGet("orderType", $orderType);

        $importables = $this->xGestService->getImportables($source, $fromDate, $toDate, $company, $orderType);
        foreach ($importables as &$item) {
            if (isset($item['lines']) && is_array($item['lines'])) {
                foreach ($item['lines'] as &$line) {
                    $line['priority'] = 0; // Poner prioridad "Media" por defecto a todas las líneas
                }
            }
        }

        return $this->json($importables);
    }

    /**
     * @Route("/xgest-import", methods={"POST"})
     */
    public function xgestImport(Request $request, XGestRestClient $xGestRestClient): Response {
        $me = $this->getUser();

        $deliveryNotes = $xGestRestClient->getDeliveryNotes();

        $deliveryNoteId = $request->request->get("deliveryNoteId");
        $line = $request->request->get("lineNumber");
        $serialNumber = $request->request->get("serialNumber");
        $technician = $request->request->get("technician");
        $jobType = $request->request->get("jobType");
        $priority = (int) $request->request->get("priority");

        if (!Obj::isNullOrEmpty($deliveryNoteId) && !Obj::isNullOrEmpty($line)) {

            $deliveryNoteLine = from($deliveryNotes)->mapMany(fn($x) => $x["lines"])
                                                    ->first(fn($x) => $x["deliveryNoteId"] == $deliveryNoteId && $x["lineNumber"] == $line);


            if ($deliveryNoteLine) {

                $deliveryNote = from($deliveryNotes)->first(fn($x) => $x["deliveryNoteId"] == $deliveryNoteLine["deliveryNoteId"]);


                $receiver = $this->userRepository->find($technician);
                $jobType = $this->jobTypeRepository->find($jobType);

                $article = new Article();
                $article->setOwner($me);
                $article->setReceiver($receiver);

                $article->setJobType($jobType);
                $article->setDescription($deliveryNoteLine["articleDescription"]);
                $article->setCode($deliveryNoteLine["articleCode"]);
                $article->setSerialNumber($deliveryNoteLine["serialNumber"]);
                $article->setLineNumber($deliveryNoteLine["lineNumber"]);
                $article->setDeliveryNoteId($deliveryNoteLine["deliveryNoteId"]);
                $article->setCustomer($deliveryNote["customerName"]);
                $article->setCompanyId($deliveryNote["companyId"]);
                $article->setPriority($priority);

                $this->em->persist($article);

                $movement = new Movement();
                $movement->setArticle($article);
                $movement->setDate(new \DateTime());
                $movement->setOldOwner(null);
                $movement->setNewOwner($me);
                $movement->setOldReceiver(null);
                $movement->setNewReceiver($receiver);
                $movement->setOldJobType(null);
                $movement->setNewJobType($jobType);
                $movement->setResponsibleUser($me);

                $this->em->persist($movement);
                $this->em->flush();

                return $this->json(null, 200);
            }
        } else {
            $stock = $xGestRestClient->getStock([27,15]);
            $item = from($stock)->first(fn($x) => in_array($serialNumber, $x["serial_numbers"]));

            if ($item) {


                $receiver = $this->userRepository->find($technician);
                $jobType = $this->jobTypeRepository->find($jobType);

                $article = new Article();
                $article->setOwner($me);
                $article->setReceiver($receiver);

                $article->setJobType($jobType);
                $article->setDescription(null);
                $article->setCode($item["article_code"]);
                $article->setSerialNumber($serialNumber);
                $article->setLineNumber(null);
                $article->setDeliveryNoteId(null);
                $article->setCustomer(null);
                $article->setCompanyId(null);
                $article->setPriority($priority);

                $this->em->persist($article);

                $movement = new Movement();
                $movement->setArticle($article);
                $movement->setDate(new \DateTime());
                $movement->setOldOwner(null);
                $movement->setNewOwner($me);
                $movement->setOldReceiver(null);
                $movement->setNewReceiver($receiver);
                $movement->setOldJobType(null);
                $movement->setNewJobType($jobType);
                $movement->setResponsibleUser($me);

                $this->em->persist($movement);
                $this->em->flush();

                return $this->json(null, 200);
            }
        }
        return $this->json(null, 400);

    }

    /**
     * @Route("/park/{input}", name="park", methods={"POST"})
     * @throws NonUniqueResultException
     */
    public function park($input, EntityManagerInterface $em): Response {

        $me = $this->getUser();
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->boxService->park($me->getId(), $input);

        return $this->json($this->boxService->getBoxes($me->getId()));
    }

    /**
     * @Route("/", name="get_boxes", methods={"GET"})
     */
    public function getBoxes(EntityManagerInterface $em): Response {
        $user = $this->getUser();
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        return $this->json($this->boxService->getBoxes($user->getId()));
    }

    /**
     * @Route("/start-timer/{id}", name="start_timer", methods={"POST"})
     * @throws NonUniqueResultException
     */
    public function startTimer($id): Response {
        $me = $this->getUser();
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->boxService->startTimeEntry($me->getId(), $id);
        return new Response();
    }

    /**
     * @Route("/stop-timer", name="stop_timer", methods={"POST"})
     * @throws NonUniqueResultException
     */
    public function stopTimer(): Response {
        $me = $this->getUser();
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->boxService->stopTimeEntries($me->getId());
        return new Response();
    }

    /**
     * @Route("/technicians-summary/", methods={"GET"})
     */
    public function getTechniciansSummary(EntityManagerInterface $em) {
        $output = [];
        foreach ($this->userRepository->findAll() as $user) {

            $inbox = $em->createQueryBuilder()
                        ->select('count(0) as count, sum(j.pointsPerCompletion) as points')
                        ->from(Article::class, 'a')
                        ->innerJoin('a.jobType', 'j')
                        ->where('a.receiver = :receiver and j.finishes = false')
                        ->orWhere('a.owner  = :receiver and a.parked = true  and j.finishes = false')
                        ->setParameter('receiver', $user->getId())
                        ->getQuery()
                        ->getResult()[0];

            $mybox = $em->createQueryBuilder()
                        ->select('count(0) as count, sum(j.pointsPerCompletion) as points')
                        ->from(Article::class, 'a')
                        ->innerJoin('a.jobType', 'j')
                        ->where('a.owner = :owner')
                        ->andWhere("j.finishes = false")
                        ->andWhere('a.receiver IS NULL')
                        ->andWhere('a.parked = false')
                        ->setParameter('owner', $user->getId())
                        ->getQuery()
                        ->getResult()[0];
            $item = [
                "id"        => $user->getId(),
                "firstName" => $user->getFirstName(),
                "lastName"  => $user->getLastName(),
                "fullName"  => $user->getFullName(),
                "n_jobs"    => ($inbox["count"] ?? 0) + ($mybox["count"] ?? 0),
                "time"      => ($inbox["points"] ?? 0) + ($mybox["points"] ?? 0)
            ];
            $output[] = $item;
        }


        return $this->json($output);

    }

    /**
     * @Route("/receive/{input}", name="receive", methods={"POST"})
     * @throws NonUniqueResultException
     */
    public function receive(string $input, EntityManagerInterface $em): Response {
        $ids = explode(',', $input);
        $me = $this->getUser();
        $meId = $me->getId();

        $this->boxService->acceptTransfer($meId, $ids);

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */

        return $this->json($this->boxService->getBoxes($meId));
    }

    /**
     * @Route("/send/{input}", name="send", methods={"POST"})
     */
    public function send(string $input, Request $request, EntityManagerInterface $em): Response {
        $ids = explode(',', $input);
        $jobTypeId = $request->request->get('jobType');
        $to = $request->request->get('to');
        $me = $this->getUser();
        $serialNumber = $request->request->get('serialNumber');

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->boxService->proposeTransfer($me->getId(), $to, $ids, $jobTypeId);

        $article = $this->articleRepository->findOneBySerialNumber($serialNumber);

        $deliveryNoteId = $article->getDeliveryNoteId();
        $companyId = $article->getCompanyId();

        $completedOrderMessage = "";

        if ($companyId && $this->boxService->getNumOfArticlesLeftOfDeliveryNote($deliveryNoteId, $companyId) === 0) {
            $completedOrderMessage = "Pedido $deliveryNoteId terminado!";
        }

        $output = array_merge(
            $this->boxService->getBoxes($me->getId()),
            ["message" => $completedOrderMessage]
        );

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        return $this->json($output);
    }

    /**
     * @Route("/update-priority/{id}", name="update_priority", methods={"POST"})
     * @throws NonUniqueResultException
     */
    public function updatePriority(Request $request, $id): Response {
        $newPriorityValue = $request->request->get("newPriorityValue");
        $this->articleRepository->updatePriorityById($id, (int)$newPriorityValue);
        return new Response();
    }

}



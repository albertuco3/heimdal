<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleJobTimeEntry;
use App\Entity\Movement;
use App\Repository\ArticleJobTimeEntryRepository;
use App\Repository\ArticleRepository;
use App\Repository\JobTypeRepository;
use App\Repository\MovementRepository;
use App\Repository\UserRepository;
use App\Service\BoxService;
use App\Service\ExcelGenerator;
use App\Service\XGestRestClient;
use DateTime;
use Develia\Date;
use Develia\From;
use Develia\TimeSpan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/traceability",name="app_traceability_")
 */
class TraceabilityController extends AbstractController {
    private UserRepository $userRepository;
    private MovementRepository $movementRepository;
    private ArticleRepository $articleRepository;
    private Security $security;
    private JobTypeRepository $jobTypeRepository;
    private BoxService $boxService;
    private ArticleJobTimeEntryRepository $articleJobTimeEntryRepository;
    private ExcelGenerator $excelGenerator;

    public function __construct(UserRepository                $userRepository,
                                JobTypeRepository             $jobTypeRepository,
                                MovementRepository            $movementRepository,
                                ArticleRepository             $articleRepository,
                                Security                      $security,
                                ArticleJobTimeEntryRepository $articleJobTimeEntryRepository,
                                BoxService                    $boxService,
                                ExcelGenerator                $excelGenerator) {

        $this->userRepository = $userRepository;

        $this->movementRepository = $movementRepository;
        $this->articleRepository = $articleRepository;
        $this->security = $security;
        $this->jobTypeRepository = $jobTypeRepository;
        $this->boxService = $boxService;
        $this->articleJobTimeEntryRepository = $articleJobTimeEntryRepository;
        $this->excelGenerator = $excelGenerator;
    }

    /**
     * @Route("/boxes",name="boxes")
     */
    public function index(Request $request) {
        return $this->render('traceability/boxes.html.twig', []);
    }

    /**
     * @Route("/by-imei",name="by_imei")
     */
    public function byImei(Request $request) {


        $movements = null;
        $owner = null;
        $receiver = null;
        $serialNumber = null;
        $job = null;
        $deliveryNoteId = null;
        $description = null;
        $code = null;
        $customer = null;

        if ($request->getMethod() == "POST") {
            $serialNumber = $request->request->get("serialNumber");
            $movements = from($this->movementRepository->findBySerialNumber($serialNumber))
                ->orderBy(fn(Movement $x) => $x->getDate(), "desc")
                ->toArray(false);

            if ($article = $this->articleRepository->findOneBySerialNumber($serialNumber)) {
                if ($owner = $article->getOwner()) {
                    $owner = $owner->getFirstName() . " " . $owner->getLastName();
                }

                if ($receiver = $article->getReceiver()) {
                    $receiver = $receiver->getFirstName() . " " . $receiver->getLastName();
                }

                if ($jobType = $article->getJobType()) {
                    $job = $jobType->getDescription();
                }

                $description = $article->getDescription();
                $code = $article->getCode();
                $deliveryNoteId = $article->getDeliveryNoteId();
                $customer = $article->getCustomer();

            }
        }


        return $this->render('traceability/by-imei.html.twig', [
            "owner"          => $owner,
            "receiver"       => $receiver,
            "serialNumber"   => $serialNumber,
            "job"            => $job,
            "deliveryNoteId" => $deliveryNoteId,
            "description"    => $description,
            "code"           => $code,
            "customer"       => $customer,
            "articleUpdates" => $movements
        ]);
    }

    /**
     * @Route("/by-order",name="by_order")
     */
    public function byOrder(Request $request) {


        $deliveryNoteId = $request->request->get("deliveryNoteId");
        if ($request->getMethod() == "POST") {

            $articles = $this->articleRepository->findByDeliveryOrder($deliveryNoteId);
        } else {
            $articles = [];
        }


        return $this->render('traceability/by-order.html.twig', [
            "articles"       => from($articles)->map(fn($x) => $this->articleToDTO($x))->toArray(),
            "deliveryNoteId" => $deliveryNoteId
        ]);
    }

    /**
     * @param $x
     * @param mixed $jobType
     * @param mixed $owner
     * @param mixed $receiver
     * @return array
     */
    private function articleToDTO(Article $x): array {
        $jobType = $x->getJobType();
        $owner = $x->getOwner();
        $receiver = $x->getReceiver();

        return [
            "code"                  => $x->getCode(),
            "description"           => $x->getDescription(),
            "id"                    => $x->getId(),
            "customer"              => $x->getCustomer(),
            "delivery_note_id"      => $x->getDeliveryNoteId(),
            "serial_number"         => $x->getSerialNumber(),
            "job_type_id"           => $jobType?->getId(),
            "job_type_description"  => $jobType?->getDescription(),
            "owner_id"              => $owner?->getId(),
            "owner_display_name"    => $owner ? $owner->getFirstName() . " " . $owner->getLastName() : null,
            "receiver_id"           => $receiver?->getId(),
            "receiver_display_name" => $receiver ? $receiver->getFirstName() . " " . $receiver->getLastName() : null,
        ];
    }

    /**
     * @Route("/by-technician",name="by_technician")
     */
    public function byTechnician(Request $request) {
        $technician = $request->request->get("technician");
        $deliveryNoteId = $request->request->get("deliveryNoteId");
        $serialNumber = $request->request->get("serialNumber");

        if ($request->getMethod() == "POST") {

            $fromDate = null;
            if ($fromDateStr = $request->request->get("fromDate")) {
                $fromDate = Date::startOfDay(DateTime::createFromFormat('Y-m-d', $fromDateStr));
            }

            $toDate = null;
            if ($toDateStr = $request->request->get("toDate")) {
                $toDate = Date::endOfDay(DateTime::createFromFormat('Y-m-d', $toDateStr));
            }

            $movements = from($this->movementRepository->findByTechnician($technician, $fromDate, $toDate))
                ->orderBy(fn(Movement $x) => $x->getDate(), "desc")
                ->toArray(false);
            if ($deliveryNoteId) {
                $movements = from($movements)->filter(fn(Movement $x) => $x->getArticle()->getDeliveryNoteId() == $deliveryNoteId)->toArray(false);
            }

            if ($serialNumber) {
                $movements = from($movements)->filter(fn(Movement $x) => $x->getArticle()->getSerialNumber() == $serialNumber)->toArray(false);
            }

        } else {
            $movements = [];
        }


        return $this->render('traceability/by-technician.html.twig', [
            "articleUpdates" => $movements,
            "technicians"    => $this->userRepository->findAll(),
            'technician'     => $technician,
            'boxes'          => $technician ? $this->boxService->getBoxes($technician) : null,
            "deliveryNoteId" => $deliveryNoteId,
            "serialNumber"   => $serialNumber
        ]);
    }

    /**
     * @Route("/import-from-xgest", name="import_from_xgest", methods={"GET","POST"})
     */
    public function importFromXGest(Request $request, XGestRestClient $xGestHttpClient, EntityManagerInterface $em) {
        return $this->render('traceability/import-from-xgest.html.twig', []);
    }

    /**
     * @Route("/performance-excel", name="performance_excel", methods={"GET","POST"})
     */
    public function performace_excel(\Develia\Request $request) {


        $form = $request->getForm();
        if ($request->getMethod() == 'POST' && $form->tryGetInt("technician", $technicianId)) {

            $fromDate = $form->tryGetDate("fromDate", $fromDate) ? Date::startOfDay($fromDate) : null;
            $toDate = $form->tryGetDate("toDate", $toDate) ? Date::endOfDay($toDate) : null;

            $this->excelGenerator->generate_performance_excel($technicianId, $fromDate, $toDate);

        }
    }

    /**
     * @Route("/performance", name="performance", methods={"GET","POST"})
     */
    public function performance(Request $request) {


        $technicianId = null;
        $performance = null;

        $fromDateStr = null;
        $toDateStr = null;

        if ($request->getMethod() == 'POST') {

            $technicianId = $request->request->get("technician");

            $fromDate = null;
            if ($fromDateStr = $request->request->get("fromDate")) {
                $fromDate = Date::startOfDay(DateTime::createFromFormat('Y-m-d', $fromDateStr));
            }

            $toDate = null;
            if ($toDateStr = $request->request->get("toDate")) {
                $toDate = Date::endOfDay(DateTime::createFromFormat('Y-m-d', $toDateStr));
            }

            $performance = $this->boxService->getPerformance($technicianId, $fromDate, $toDate);

        }


        if ($this->security->isGranted("ROLE_TECHNICIAN_SUPERVISOR")) {
            $technicians = $this->userRepository->findAll();
        } else {
            $technicians = [$this->userRepository->getCurrentUser()];
        }

        return $this->render('traceability/performance.twig', [
            "performance"  => $performance,
            "technicians"  => $technicians,
            "technicianId" => $technicianId,
            "fromDate"     => $fromDateStr,
            "toDate"       => $toDateStr,
            "jobs"         => $this->jobTypeRepository->findBy([
                "deletionDate" => null,
                "finishes"     => false
            ]),
            "averages"     => from($this->articleJobTimeEntryRepository->findAll())
                ->groupBy(fn(ArticleJobTimeEntry $ajte) => $ajte->getJobType()->getDescription(), function (From $x) {
                    return TimeSpan::fromSeconds($x->average(function (ArticleJobTimeEntry $timeEntry) {
                        return TimeSpan::fromDateDifference($timeEntry->getStart(),
                            $timeEntry->getEnd() ?? new \DateTime())->getSeconds();
                    }))->format("mm:ss");
                })
                ->orderKeysBy(fn($x) => $x)
                ->toArray()
        ]);
    }
}
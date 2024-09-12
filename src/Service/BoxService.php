<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\ArticleJobTimeEntry;
use App\Entity\JobTypeTransition;
use App\Entity\Movement;
use App\Entity\User;
use App\Repository\ArticleJobTimeEntryRepository;
use App\Repository\ArticleRepository;
use App\Repository\JobTypeRepository;
use App\Repository\JobTypeTransitionRepository;
use App\Repository\MovementRepository;
use App\Repository\UserPointRepository;
use App\Repository\UserRepository;
use App\Service\XGestService;
use Develia\Date;
use Develia\From;
use Develia\Math;
use Develia\TimeSpan;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\User\UserInterface;

class BoxService {
    public function __construct(EntityManagerInterface $em,
        UserRepository $userRepository,
        JobTypeRepository $jobTypeRepository,
        ArticleRepository $articleRepository,
        MovementRepository $movementRepository,
        JobTypeTransitionRepository $jobTypeTransitionRepository,
        ArticleJobTimeEntryRepository $articleJobTimeEntryRepository,
        UserPointRepository $userPointRepository,
        XGestService $XGestService
    ) {

        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->jobTypeRepository = $jobTypeRepository;
        $this->articleRepository = $articleRepository;
        $this->movementRepository = $movementRepository;
        $this->jobTypeTransitionRepository = $jobTypeTransitionRepository;
        $this->articleJobTimeEntryRepository = $articleJobTimeEntryRepository;
        $this->userPointRepository = $userPointRepository;
        $this->XGestService = $XGestService;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function startTimeEntry(int $userId, int $articleId): void {
        $user = $this->userRepository->find($userId);
        $article = $this->articleRepository->find($articleId);


        $this->stopTimeEntries($userId);

        $now = new \DateTime();

        $newTimeEntry = new ArticleJobTimeEntry();
        $newTimeEntry->setStart($now);
        $newTimeEntry->setEnd(null);
        $newTimeEntry->setJobType($article->getJobType());
        $newTimeEntry->setTechnician($user);
        $newTimeEntry->setArticle($article);


        $this->em->persist($newTimeEntry);
        $this->em->flush();

    }


    /**
     * @throws \ReflectionException
     */
    public function getTimeSpent(int $userId, int $articleId): float|int {
        return from($this->em->createQueryBuilder()
                             ->select("ajte.start,ajte.end")
                             ->from(ArticleJobTimeEntry::class, "ajte")
                             ->where("ajte.technician = :technician and ajte.article = :article")
                             ->setParameter("technician", $userId)
                             ->setParameter("article", $articleId)
                             ->getQuery()
                             ->getResult())->sum(fn($x) => (int)Date::difference($x["end"] ?? Date::now(), $x["start"])->getSeconds());
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getActiveTimeEntry(int $userId, ?int $article = null): ?ArticleJobTimeEntry {


        $queryBuilder = $this->em->createQueryBuilder()
                                 ->select("ajte")
                                 ->from(ArticleJobTimeEntry::class, "ajte")
                                 ->where("ajte.technician = :technician and ajte.end is null")
                                 ->setParameter("technician", $userId);

        if ($article)
            $queryBuilder = $queryBuilder->andWhere("ajte.article = :article")->setParameter("article", $article);

        return $queryBuilder->getQuery()
                            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function stopTimeEntries(int $userId): array {
        $output = [];

        $now = new \DateTime();


        while ($oldTimeEntry = $this->getActiveTimeEntry($userId)) {

            $oldTimeEntry->setEnd($now);
            $this->em->persist($oldTimeEntry);
            $this->em->flush();
            $output[] = $output;

        }


        return $output;

    }

    /**
     * @throws NonUniqueResultException
     */
    public function stopTimeEntry(int $userId, int $article): bool {


        $now = new \DateTime();

        if ($activeTimeEntry = $this->getActiveTimeEntry($userId, $article)) {
            $activeTimeEntry->setEnd($now);
            $this->em->persist($activeTimeEntry);
            $this->em->flush();

            return true;
        }

        return false;


    }

    /**
     * @throws NonUniqueResultException
     */
    public function park(int $userId, int $article): bool {


        $article = $this->articleRepository->findOneBy(["id" => $article, "owner" => $userId]);
        if ($article) {
            $article->setParked(true);
            $this->stopTimeEntry($userId, $article->getId());

            $this->em->persist($article);
            $this->em->flush();

            return true;
        }

        return false;

    }

    /**
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function acceptTransfer(int|UserInterface|User $user, array $articles): void {
        $user = $this->userRepository->find($user);

        $articles = $this->articleRepository->createQueryBuilder('a')
                                            ->where('a.receiver = :me and a.id in (:ids)')
                                            ->orWhere('a.owner = :me AND a.parked = true and a.id in (:ids)')
                                            ->setParameter('me', $user)
                                            ->setParameter('ids', $articles)
                                            ->getQuery()
                                            ->getResult();


        foreach ($articles as $article) {

            $movement = new Movement();
            $movement->setArticle($article);
            $movement->setDate(new \DateTime());


            $movement->setOldOwner($article->getOwner());
            $movement->setNewOwner($user);
            $movement->setOldReceiver($user);
            $movement->setNewReceiver(null);
            $movement->setNewJobType($article->getJobType());
            $movement->setOldJobType($article->getJobType());
            $movement->setResponsibleUser($user);


            $article->setReceiver(null);
            $article->setParked(false);
            $article->setOwner($user);

            $this->em->persist($article);
            $this->em->persist($movement);
        }

        if (!$this->getActiveTimeEntry($user->getId()))
            $this->startTimeEntry($user->getId(), from($articles)->last()->getId());

        $this->em->flush();
    }

    public function getPerformance(int $userId, \DateTimeInterface $fromDate = null, \DateTimeInterface $toDate = null): array {
        /*$output = from($this->movementRepository->findByTechnician($userId, $fromDate, $toDate))
            ->filter(fn(Movement $x) => (!$x->getOldOwner() || $x->getOldOwner()->getId() == $userId) ||
                                        (!$x->getNewOwner() || $x->getNewOwner()->getId() != $userId))
            ->groupBy(fn(Movement $x) => $x->getDate()->format("Y-m-d"))
            ->map(fn(From $x) => $x->groupBy(fn($y) => $y->getOldJobType()?->getId())
                                   ->filter(fn($k, $v) => $k)
                                   ->map(fn($y) => $y->sum(fn(Movement $z) => $z?->getOldJobType()?->getPointsPerCompletion() ?? 0))
                                   ->toArray())
            ->toArray();

        return $output;*/

        $transitions = $this->jobTypeTransitionRepository->findAll();


        $timeEntries = $this->articleJobTimeEntryRepository->findByTechnician($userId, $fromDate, $toDate);
        $movements = $this->movementRepository->findByTechnician($userId, $fromDate, $toDate);
        $points = from($this->userPointRepository->getPoints($userId, $fromDate, $toDate))
            ->groupBy(fn($x) => $x->getDate()->format("Y-m-d"))
            ->map(fn($x) => $x->sum(fn($y) => $y->getPoints()))
            ->toArray();


        $output = from($movements)
            ->filter(fn(Movement $x) => ($x->getOldOwner()?->getId() == $userId))
            ->filter(fn(Movement $x) => $x->getOldJobType())
            ->map(fn(Movement $x) => [
                "jobType" => $x->getOldJobType(),
                "date" => $x->getDate(),
                "points" => from($transitions)->first(function (JobTypeTransition $transition) use ($x) {
                        return $transition->getToJobType()->getId() == $x->getNewJobType()->getId() &&
                               $transition->getFromJobType()->getId() == $x->getOldJobType()->getId();
                    })?->getPointsPerCompletion() ?? $this->jobTypeRepository->findOneBy(["id" => $x->getOldJobType()->getId()])
                                                                             ->getPointsPerCompletion() ?? 0
            ])
            ->groupBy(fn($x) => $x["date"]->format("Y-m-d"))
            ->map(function (string $date, From $movement) use ($points, $transitions, $timeEntries) {
                return $movement->groupBy(fn($entry) => $entry["jobType"]->getId())
                                ->map(function ($jobTypeId, From $movement2) use ($points, $transitions, $date, $timeEntries) {

                                    $dedicatedSeconds = from($timeEntries)->filter(fn(ArticleJobTimeEntry $x) => $x->getJobType()->getId() == $jobTypeId)
                                                                          ->filter(fn(ArticleJobTimeEntry $x) => $x->getStart()->format("Y-m-d") == $date)
                                                                          ->map(fn($x) => Math::round(TimeSpan::fromDateDifference($x->getStart(),
                                                                              $x->getEnd() ?? new \DateTime())->getSeconds()), 0)
                                                                          ->toArray();

                                    $tmp = $movement2->sum(fn($x) => $x["points"]);
                                    return [
                                        "points" => $tmp,
                                        "time" => from($dedicatedSeconds)->sum(),
                                        "averageTime" => from($dedicatedSeconds)->filter(fn($x) => $x > 0)->average() ?? 0,
                                        "done" => $movement2->count() > 0 ? ($tmp / $movement2->count()) : 0
                                    ];

                                })->toArray();
            })
            ->orderKeysBy(fn($x) => $x)
            ->toArray();


        foreach ($points as $date => $point) {
            if (!array_key_exists($date, $output))
                $output[$date] = [];

            $output[$date]["manual"] = [
                "points" => $point,
                "time" => 0,
                "averageTime" => 0
            ];
        }

        foreach ($output as $date => &$jobData) {

            $jobData["total"] = [
                "points" => from($jobData)->sum(fn($x) => $x["points"]) ?? 0,
                "time" => from($jobData)->sum(fn($x) => $x["time"]) ?? 0,
                "averageTime" => from($jobData)->sum(fn($x) => $x["averageTime"]) ?? 0
            ];

        }


        $output["total"] = from($output)
            ->mapMany(function ($jobData) {
                foreach ($jobData as $jobKey => $data) {
                    yield $jobKey => $data;
                }
            })
            ->groupBy(fn($k, $v) => $k, function ($jobKey, $data) {
                return [
                    "points" => $data->sum(fn($x) => $x["points"]) ?? 0,
                    "time" => $data->sum(fn($x) => $x["time"]) ?? 0,
                    "averageTime" => $data->sum(fn($x) => $x["averageTime"]) ?? 0
                ];
            })->toArray();

        if (!$output["total"])
            $output["total"]["total"] = [
                "points" => 0,
                "time" => 0,
                "averageTime" => "?"
            ];


        return $output;
    }

    /**
     * @param $x
     * @param mixed $jobType
     * @param mixed $owner
     * @param mixed $receiver
     * @return array
     * @throws NonUniqueResultException
     */
    private function articleToDTO(Article $x): array {
        $jobType = $x->getJobType();
        $owner = $x->getOwner();
        $receiver = $x->getReceiver();

        return [
            "code" => $x->getCode(),
            "description" => $x->getDescription(),
            "id" => $x->getId(),
            "customer" => $x->getCustomer(),
            "delivery_note_id" => $x->getDeliveryNoteId(),
            "serial_number" => $x->getSerialNumber(),
            "job_type_id" => $jobType?->getId(),
            "job_type_description" => $jobType?->getDescription(),
            "owner_id" => $owner?->getId(),
            "owner_display_name" => $owner ? $owner->getFirstName() . " " . $owner->getLastName() : null,
            "receiver_id" => $receiver?->getId(),
            "receiver_display_name" => $receiver ? $receiver->getFirstName() . " " . $receiver->getLastName() : null,
            "parked" => $x->isParked(),
            "timer" => $this->getTimeSpent($owner->getId(), $x->getId()),
            "active" => $this->getActiveTimeEntry($owner->getId())?->getArticle()?->getId() == $x->getId(),
            "priority" => $x->getTranslatedPriority(),
            "priority_value" => $x->getPriority(),
        ];
    }

    function getBoxes(int $userId) {
        $inbox = from($this->getInbox($userId))
            ->map(fn($x) => $this->articleToDTO($x))
            ->toArray();

        $mybox = from($this->getMybox($userId))
            ->map(fn($x) => $this->articleToDTO($x))
            ->toArray();

        $outbox = from($this->getOutbox($userId))
            ->map(fn($x) => $this->articleToDTO($x))
            ->toArray();


        $from = Date::startOfDay(Date::now());
        $to = Date::endOfDay(Date::now());

        $performance = $this->getPerformance($userId, $from, $to);

        $output = [
            'inbox' => $inbox,
            'outbox' => $outbox,
            'mybox' => $mybox,
            'performance' => $performance["total"]["total"]["points"],
        ];

        return $output;
    }

    function getNumOfArticlesLeftOfDeliveryNote(string $deliveryNoteId, string $companyId): int {
        // Compruebo si hay artículos sin finalizar en XGest con ese delivery note que no hayan sido importados
        $numArticlesLeftInXGest = 0;

        $importables = $this->XGestService->getImportableByOrder($deliveryNoteId, $companyId);

        if ($importables != null && is_array($importables) && array_key_exists('lines', $importables)) {
            $numArticlesLeftInXGest = count($importables['lines']);
        }

        // Compruebo si hay artículos sin finalizar en nuestra propia BD
        $numArticlesLeftInDB = $this->em->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from(Article::class, 'a')
            ->innerJoin('a.jobType', 'j')
            ->where('a.deliveryNoteId = :deliveryNoteId')
            ->andWhere('j.finishes != 1')
            ->setParameter('deliveryNoteId', $deliveryNoteId)
            ->getQuery()
            ->getSingleScalarResult();

        return $numArticlesLeftInXGest + $numArticlesLeftInDB;
    }

    /**
     * @param $user
     * @return Article[]
     */
    public function getMybox($user): array {
        return $this->em->createQueryBuilder()
                        ->select('a')
                        ->from(Article::class, 'a')
                        ->innerJoin('a.jobType', 'j')
                        ->where('a.owner = :owner')
                        ->andWhere("j.finishes = false")
                        ->andWhere('a.receiver IS NULL')
                        ->andWhere('a.parked = false')
                        ->setParameter('owner', $user)
                        ->getQuery()
                        ->getResult();
    }

    /**
     * @param $user
     * @return Article[]
     */
    public function getOutbox($user): array {
        return $this->em->createQueryBuilder()
                        ->select('a')
                        ->from(Article::class, 'a')
                        ->innerJoin('a.jobType', 'j')
                        ->where('a.owner = :owner')
                        ->andWhere("j.finishes = false")
                        ->andWhere('a.receiver IS NOT NULL')
                        ->andWhere('a.parked = false')
                        ->setParameter('owner', $user)
                        ->getQuery()
                        ->getResult();
    }

    /**
     * @param $user
     * @return Article[]
     */
    public function getInbox($user): array {
        return $this->em->createQueryBuilder()
                        ->select('a')
                        ->from(Article::class, 'a')
                        ->innerJoin('a.jobType', 'j')
                        ->where('a.receiver = :receiver and j.finishes = false')
                        ->orWhere('a.owner  = :receiver and a.parked = true  and j.finishes = false')
                        ->setParameter('receiver', $user)
                        ->getQuery()
                        ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function proposeTransfer(int $fromUserId, ?int $toUserId, array $articles, int $jobType = null): void {


        $jobFinishes = false;
        if ($jobType) {
            $jobType = $this->jobTypeRepository->find($jobType);
            $jobFinishes = $jobType->getFinishes();
        }

        $fromUser = $this->userRepository->find($fromUserId);

        $toUser = null;
        if (!$jobFinishes) {
            $toUser = $this->userRepository->find($toUserId);
            if (!$toUser)
                throw new \InvalidArgumentException("Receiver must not be null");
        }


        foreach ($articles as $id) {

            $article = $this->articleRepository->findOneBy(['id' => $id, 'owner' => $fromUser]);

            if ($article) {

                $movement = new Movement();
                $movement->setArticle($article);
                $movement->setDate(new \DateTime());

                $movement->setOldOwner($fromUser);

                $movement->setNewOwner($jobFinishes ? null : $fromUser);
                $movement->setOldReceiver(null);
                $movement->setNewReceiver($jobFinishes ? null : $toUser);
                $movement->setOldJobType($article->getJobType());
                $movement->setNewJobType($jobType);
                $movement->setResponsibleUser($fromUser);

                $article->setOwner($jobFinishes ? null : $article->getOwner());
                $article->setReceiver($jobFinishes ? null : $toUser);
                $article->setJobType($jobType);

                $this->em->persist($article);
                $this->em->persist($movement);

                $this->stopTimeEntry($fromUserId, $article->getId());
            }
        }


        $this->em->flush();
    }

    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private JobTypeRepository $jobTypeRepository;
    private ArticleRepository $articleRepository;
    private MovementRepository $movementRepository;
    private JobTypeTransitionRepository $jobTypeTransitionRepository;
    private ArticleJobTimeEntryRepository $articleJobTimeEntryRepository;
    private UserPointRepository $userPointRepository;
    private XGestService $XGestService;
}
<?php

namespace App\Controller\API\V1;

use App\Entity\JobType;
use App\Entity\JobTypeTransition;
use App\Repository\JobTypeRepository;
use App\Repository\JobTypeTransitionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api/v1/job-types", name="job-types_")
 */
class JobTypesController extends AbstractController {
    private Security $security;
    private JobTypeRepository $jobTypeRepository;
    private EntityManagerInterface $em;
    private JobTypeTransitionRepository $jobTypeTransitionRepository;

    public function __construct(Security                    $security,
                                JobTypeRepository           $jobTypeRepository,
                                EntityManagerInterface      $em,
                                JobTypeTransitionRepository $jobTypeTransitionRepository) {
        $this->security = $security;
        $this->jobTypeRepository = $jobTypeRepository;

        $this->em = $em;
        $this->jobTypeTransitionRepository = $jobTypeTransitionRepository;
    }

    /**
     * @Route("/", name="get_all", methods={"GET"})
     */
    public function getAll(): Response {
        return $this->json(from($this->jobTypeRepository->findBy(["deletionDate" => null]))
                               ->orderBy(fn(JobType $x) => $x->getDescription())
                               ->map(fn($x) => $this->jobToDto($x))
                               ->toArray(false));
    }

    private function jobToDto(?JobType $jobType) {

        return $jobType ? [
            "id"          => $jobType->getId(),
            "description" => $jobType->getDescription(),
            "finishes"    => $jobType->getFinishes(),
            "transitions" => from($jobType->getTransitions())->map(fn($y) => [
                "transitionId"        => $y->getId(),
                "toJob"               => $y->getToJobType()?->getId(),
                "fromJob"             => $y->getFromJobType()?->getId(),
                "pointsPerCompletion" => $y->getPointsPerCompletion() ?? 0,
            ])->toArray(),
        ] : null;
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     */
    public function create(Request $request): Response {
        if (!$this->security->isGranted('ROLE_TECHNICIAN_SUPERVISOR')) {
            throw $this->createAccessDeniedException();
        }

        $post = $request->request;

        $jobType = new JobType();
        $jobType->setDescription($post->get("description"));
        $jobType->setPointsPerCompletion($post->get("pointsPerCompletion") ?? 0.0);
        $jobType->setFinishes($post->get("finishes") == "true");
        $transitionsSrc = $post->get("transitions") ?? [];
        $transitionsDst = $jobType->getTransitions();

        foreach ($transitionsSrc as $transitionSrc) {

            $transition = new JobTypeTransition();
            $transition->setPointsPerCompletion($transitionSrc["pointsPerCompletion"]);
            $transition->setToJobType($this->jobTypeRepository->find($transitionSrc["toJob"]));
            $transition->setFromJobType($jobType);


            $transitionsDst->add($transition);
            $this->em->persist($transition);

        }
        $this->em->persist($jobType);
        $this->em->flush();

        return $this->json($this->jobToDto($jobType));
    }

    /**
     * @Route("/{id}", name="update", methods={"POST"})
     */
    public function update(Request $request, $id): Response {
        if (!$this->security->isGranted('ROLE_TECHNICIAN_SUPERVISOR')) {
            throw $this->createAccessDeniedException();
        }

        $post = $request->request;
        $jobType = $this->jobTypeRepository->find($id);
        $jobType->setDescription($post->get("description"));
        $jobType->setPointsPerCompletion($post->get("pointsPerCompletion") ?? 0.0);
        $jobType->setFinishes($post->get("finishes") == "true");
        $transitionsDst = $jobType->getTransitions();
        $transitionsSrc = $post->get("transitions") ?? [];


        while (count($transitionsDst) > count($transitionsSrc)) {
            $index = $transitionsDst->count() - 1;
            $this->em->remove($transitionsDst[$index]);
            $transitionsDst->remove($index);
        }

        $i = 0;
        foreach ($transitionsSrc as $transitionSrc) {

            $nextJobType = $this->jobTypeRepository->find($transitionSrc["toJob"]);

            $transition = isset($transitionsDst[$i]) ? $transitionsDst[$i] : new JobTypeTransition();
            /** @var JobTypeTransition $transition */
            $transition->setPointsPerCompletion($transitionSrc["pointsPerCompletion"]);
            $transition->setToJobType($nextJobType);
            $transition->setFromJobType($jobType);

            if (!$transitionsDst->contains($transition))
                $transitionsDst->add($transition);

            $this->em->persist($transition);
            $i++;
        }
        $this->em->persist($jobType);
        $this->em->flush();

        return $this->json($this->jobToDto($jobType));
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete($id): Response {
        if (!$this->security->isGranted('ROLE_TECHNICIAN_SUPERVISOR')) {
            throw $this->createAccessDeniedException();
        }

        $jobType = $this->jobTypeRepository->find($id);
        if ($jobType) {
            $this->jobTypeRepository->remove($jobType, false);
            $this->jobTypeTransitionRepository->createQueryBuilder("jtt")
                                              ->delete(JobTypeTransition::class, "jtt")
                                              ->where("jtt.toJobType = :job")
                                              ->setParameter("job", $jobType)
                                              ->getQuery()
                                              ->getResult();

        }

        $this->em->flush();

        return new Response(null, 200);

    }


}



<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin/users")
 */
class UsersController extends AbstractController
{

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->encoder = $encoder;
        $this->em = $em;
    }

    /**
     * @Route("/",name="app_admin_users_index")
     */
    public function index(): Response
    {
        return $this->render('admin/users/index.html.twig', [
            "users" => $this->userRepository->findAll()
        ]);
    }

    /**
     * @Route("/{id}",name="app_admin_users_single", requirements={"id"="\d+"})
     */
    public function single(Request $request, $id): Response
    {

        if ($request->isMethod("GET"))
            return $this->render('admin/users/single.html.twig', [
                "user" => $this->userRepository->find($id)
            ]);


        $user = $this->userRepository->find($id);

        $user->setUsername($request->request->get("username"));
        $user->setFirstName($request->request->get("firstName"));
        $user->setLastName($request->request->get("lastName"));

        $password = $request->request->get("password");
        if ($password)
            $user->setPassword($this->encoder->encodePassword($user, $password));


        $user->setRoles(from($request->request->get("roles") ?: [])
            ->append("ROLE_USER")
            ->distinct()
            ->values()
            ->toArray());

        $user->setJobPool($request->request->get("isJobPool") ? 1 : 0);


        $this->em->persist($user);
        $this->em->flush($user);

        return $this->redirectToRoute("app_admin_users_index");

    }

    /**
     * @Route("/delete/{id}",name="app_admin_users_delete", requirements={"id"="\d+"})
     */
    public function delete($id): Response
    {
        $this->userRepository->remove($this->userRepository->find($id));
        return $this->redirectToRoute("app_admin_users_index");
    }

    /**
     * @Route("/create",name="app_admin_users_create")
     */
    public function create(Request $request): Response
    {
        if ($request->isMethod("POST")) {
            $user = new User();
            $user->setUsername($request->request->get("username"));
            $user->setFirstName($request->request->get("firstName"));
            $user->setLastName($request->request->get("lastName"));

            $password = $request->request->get("password");
            if ($password)
                $user->setPassword($this->encoder->encodePassword($user, $password));

            $user->setRoles(from($request->request->get("roles") ?: [])
                ->append("ROLE_USER")
                ->distinct()
                ->values()
                ->toArray());


            $this->em->persist($user);
            $this->em->flush();

            return $this->redirectToRoute("app_admin_users_index");
        }

        return $this->render('admin/users/create.html.twig', []);
    }

    private UserRepository $userRepository;
    private UserPasswordEncoderInterface $encoder;
    private EntityManagerInterface $em;


}
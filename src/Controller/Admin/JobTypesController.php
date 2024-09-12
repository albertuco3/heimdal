<?php

namespace App\Controller\Admin;

use App\Entity\JobType;
use App\Entity\JobTypeTransition;
use App\Repository\JobTypeRepository;
use Develia\Obj;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/job-types")
 */
class JobTypesController extends AbstractController
{


    /**
     * @Route("/", name="app_admin_jobtypes_index")
     */
    public function index(Request $request): Response
    {

        return $this->render('admin/job-types/index.html.twig');
    }





}
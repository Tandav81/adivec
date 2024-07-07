<?php

namespace App\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TeamController extends AbstractController
{
    #[Route('/team', name: 'app_team')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $members = $entityManager->getRepository(Member::class)->findAll();
        return $this->render('team/index.html.twig', [
            'controller_name' => 'TeamController',
            'members' => $members
        ]);
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\BlogPost;
use App\Entity\Family;
use App\Entity\LogoPartenaire;
use App\Entity\Members;
use App\Entity\Product;
use App\Entity\Slide;
use App\Entity\Type;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/my-dashboard.html.twig', [
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Adivec');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToUrl('Site Adivec', 'fa fa-home', url: '/'),

            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::linkToCrud('BlogPost', 'fa fa-newspaper-o', BlogPost::class),
            MenuItem::linkToCrud('Slide', 'fa fa-picture-o', Slide::class),
            MenuItem::linkToCrud('Type', 'fa fa-tags', Type::class),
            MenuItem::linkToCrud('Family', 'fa fa-tags', Family::class),
            MenuItem::linkToCrud('Product', 'fa fa-book', Product::class),
            MenuItem::linkToCrud('Logos', 'fa fa-briefcase', LogoPartenaire::class),
            MenuItem::linkToCrud('Users', 'fa fa-user', User::class),
            MenuItem::linkToCrud('Members', 'fa fa-user-circle-o', Members::class),
            MenuItem::linkToLogout('Logout', 'fa fa-exit'),
            ];
    }
}

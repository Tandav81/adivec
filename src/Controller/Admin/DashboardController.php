<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Entity\News;
use App\Entity\Family;
use App\Entity\LogoPartenaire;
use App\Entity\Member;
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
            MenuItem::linkToUrl('Adivec', 'fa fa-home', url: '/'),
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::linkToCrud('News', 'fa fa-newspaper-o', News::class),
            MenuItem::linkToCrud('Carousel', 'fa fa-picture-o', Slide::class),
            MenuItem::linkToCrud('Types', 'fa fa-tags', Type::class),
            MenuItem::linkToCrud('Familles', 'fa fa-tags', Family::class),
            MenuItem::linkToCrud('Applications', 'fa fa-book', Application::class),
            MenuItem::linkToCrud('Produits', 'fa fa-book', Product::class),
            MenuItem::linkToCrud('Partenaires', 'fa fa-briefcase', LogoPartenaire::class),
            MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', User::class),
            MenuItem::linkToLogout('Logout', 'fa fa-exit'),
            ];
    }
}

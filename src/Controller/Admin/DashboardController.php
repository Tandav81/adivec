<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Entity\BlogPost;
use App\Entity\Family;
use App\Entity\LogoPartenaire;
use App\Entity\Member;
use App\Entity\Packaging;
use App\Entity\Product;
use App\Entity\Slide;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\PageViewRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly PageViewRepository $pageViewRepository) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $today     = new \DateTimeImmutable('today');
        $week      = new \DateTimeImmutable('-7 days');
        $month     = new \DateTimeImmutable('-30 days');

        $visitsPerDay = $this->pageViewRepository->countPerDay(30);

        return $this->render('admin/my-dashboard.html.twig', [
            'stats' => [
                'today'          => $this->pageViewRepository->countSince($today),
                'week'           => $this->pageViewRepository->countSince($week),
                'month'          => $this->pageViewRepository->countSince($month),
                'uniqueVisitors' => $this->pageViewRepository->countUniqueVisitors(30),
            ],
            'topPages'     => $this->pageViewRepository->topPages(30, 10),
            'visitsPerDay' => $visitsPerDay,
            'chartLabels'  => array_keys($visitsPerDay),
            'chartData'    => array_values($visitsPerDay),
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
            MenuItem::linkToCrud('News', 'fa fa-newspaper-o', BlogPost::class),
            MenuItem::linkToCrud('Carousel', 'fa fa-picture-o', Slide::class),
            MenuItem::linkToCrud('Types', 'fa fa-tags', Type::class),
            MenuItem::linkToCrud('Familles', 'fa fa-tags', Family::class),
            MenuItem::linkToCrud('Applications', 'fa fa-book', Application::class),
            MenuItem::linkToCrud('Packagings', 'fa fa-book', Packaging::class),
            MenuItem::linkToCrud('Produits', 'fa fa-book', Product::class),
            MenuItem::linkToCrud('Partenaires', 'fa fa-briefcase', LogoPartenaire::class),
            MenuItem::linkToLogout('Logout', 'fa fa-exit'),
            ];
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\LogoPartenaire;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;

enum Direction
{
    case Top;
    case Up;
    case Down;
    case Bottom;
}

class ProductCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $productRepository
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('nom')
            ->add('type')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom'),
            AssociationField::new('fournisseur'),
            AssociationField::new('type'),
            AssociationField::new('applications')->onlyOnForms(),
            AssociationField::new('packagings')->onlyOnForms(),
            ImageField::new('image')
        ->setBasePath('uploads/images/products')
        ->setUploadDir('public/uploads/images/products')
        ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $entityCount = $this->productRepository->count([]);

        $moveTop = Action::new('moveTop', false, 'fa fa-arrow-up')
            ->setHtmlAttributes(['title' => 'Move to top'])
            ->linkToCrudAction('moveTop')
            ->displayIf(fn ($entity) => $entity->getPosition() > 0);

        $moveUp = Action::new('moveUp', false, 'fa fa-sort-up')
            ->setHtmlAttributes(['title' => 'Move up'])
            ->linkToCrudAction('moveUp')
            ->displayIf(fn ($entity) => $entity->getPosition() > 0);

        $moveDown = Action::new('moveDown', false, 'fa fa-sort-down')
            ->setHtmlAttributes(['title' => 'Move down'])
            ->linkToCrudAction('moveDown')
            ->displayIf(fn ($entity) => $entity->getPosition() < $entityCount - 1);

        $moveBottom = Action::new('moveBottom', false, 'fa fa-arrow-down')
            ->setHtmlAttributes(['title' => 'Move to bottom'])
            ->linkToCrudAction('moveBottom')
            ->displayIf(fn ($entity) => $entity->getPosition() < $entityCount - 1);

        return $actions
            ->add(Crud::PAGE_INDEX, $moveBottom)
            ->add(Crud::PAGE_INDEX, $moveDown)
            ->add(Crud::PAGE_INDEX, $moveUp)
            ->add(Crud::PAGE_INDEX, $moveTop);
    }

    public function moveTop(AdminContext $context): Response
    {
        return $this->move($context, Direction::Top);
    }

    public function moveUp(AdminContext $context): Response
    {
        return $this->move($context, Direction::Up);
    }

    public function moveDown(AdminContext $context): Response
    {
        return $this->move($context, Direction::Down);
    }

    public function moveBottom(AdminContext $context): Response
    {
        return $this->move($context, Direction::Bottom);
    }

    private function move(AdminContext $context, Direction $direction): Response
    {
        $object = $context->getEntity()->getInstance();
        $newPosition = match($direction) {
            Direction::Top => 0,
            Direction::Up => $object->getPosition() - 1,
            Direction::Down => $object->getPosition() + 1,
            Direction::Bottom => -1,
        };

        $object->setPosition($newPosition);
        $this->em->flush();

        $this->addFlash('success', 'The element has been successfully moved.');

        return $this->redirect($context->getRequest()->headers->get('referer'));
    }
}

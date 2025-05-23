<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{

    public function __construct(
        private OrderRepository $orderRepository,
    ){}

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {

        $show = Action::new('Afficher')
            ->linkToCrudAction('show');

        return $actions
            ->add(Crud::PAGE_INDEX, $show)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
        ;
    }

    public function show(AdminContext $context)
    {
        $id = $context->getRequest()->query->get('entityId');

        if (!$id) {
            throw $this->createNotFoundException("Aucun ID d'entité fourni.");
        }

        $order = $this->orderRepository->find($id);

        if (!$order) {
            throw $this->createNotFoundException("Commande #$id introuvable.");
        }

        return $this->render('admin/order.html.twig', [
            'order' => $order,
        ]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateField::new('createdAt', 'Date'),
            NumberField::new('state', 'Statut')
                ->setTemplatePath('admin/state.html.twig'),
            AssociationField::new('user', 'Utilisateur'),
            TextField::new('carrierName', 'Transporteur'),
            NumberField::new('totalTva', 'Total TVA')
                ->formatValue(function ($value, $entity) {
                    return number_format($value, 2, ',', ' ') . ' €';
                }),
            NumberField::new('totalWt', 'Total TTC')
                ->formatValue(function ($value, $entity) {
                    return number_format($value, 2, ',', ' ') . ' €';
                }),

        ];
    }
}

<?php

namespace App\Controller\Admin;

use App\Classe\Mail;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderCrudController extends AbstractCrudController
{

    public function __construct(
        private OrderRepository $orderRepository,
        private EntityManagerInterface $entityManager,
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

    public function show(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, Request $request): Response
    {
        $id = $context->getRequest()->query->get('entityId');

        if (!$id) {
            throw $this->createNotFoundException("Aucun ID d'entité fourni.");
        }

        $order = $this->orderRepository->find($id);

        if (!$order) {
            throw $this->createNotFoundException("Commande #$id introuvable.");
        }

        // Récupérer l'URL de l'action "SHOW"
        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction('show')
            ->setEntityId($id)
            ->generateUrl();

        if ($state = $request->get('state')) {
            $this->changeState($state, $order);
        }

        return $this->render('admin/order.html.twig', [
            'order' => $order,
            'current_url' => $url,
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

    private function changeState(int $state, Order $order): void {
        if (array_key_exists($state, Order::STATE) AND $state !== Order::STATE[$order->getState()]) {
            $order->setState($state);
            $this->entityManager->flush();

            $this->addFlash('success', 'Statut de la commande correctement mis à jour.');

            $vars = [
                'firstname' => $order->getUser()->getFirstName(),
                'id_order' => $order->getId(),
            ];

            $email = new Mail();
            $email->send(
                $order->getUser()->getEmail(),
                $order->getUser()->getFullName(),
                Order::STATE_EMAIL[$state]['email_subject'],
                Order::STATE_EMAIL[$state]['email_template'],
                $vars
            );
        }
    }
}

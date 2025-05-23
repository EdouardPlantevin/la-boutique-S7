<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Carrier;
use App\Repository\AddressRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('addresses', EntityType::class, [
                'label' => 'Choissisez une adresse de livraison',
                'class' => Address::class,
                'multiple' => false,
                'expanded' => true,
                'choices' => $options['addresses'],
                'label_html' => true,
                'required' => true,
            ])
            ->add('carriers', EntityType::class, [
                'label' => 'Choissisez votre transporteur',
                'class' => Carrier::class,
                'multiple' => false,
                'expanded' => true,
                'required' => true,
                'label_html' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn btn-success w-100',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'addresses' => null,
        ]);
    }
}

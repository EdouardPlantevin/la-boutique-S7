<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Produit')
            ->setEntityLabelInPlural('Produits')
        ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Titre')
                ->setHelp('Nom du produit'),
            BooleanField::new('isHomepage', 'Prduit à la Une')
                ->setHelp('Vous permet d\'afficher un produit sur la homepage'),
            SlugField::new('slug', 'URL')
                ->setTargetFieldName('name')
                ->setHelp('Url de votre produit générée automatiquement'),
            TextEditorField::new('description', 'Description')->setHelp('Description du produit'),
            ImageField::new('illustration', 'Image')
                ->setHelp('Image du produit en 600x600px')
                ->setUploadDir('public/uploads')
                ->setBasePath('uploads')
                ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
                ->setRequired($pageName !== Crud::PAGE_EDIT),
            NumberField::new('price', 'Prix HT')
                ->setHelp('Prix du produit HT sans le sigle €')
                ->formatValue(function ($value, $entity) {
                    return number_format($value, 2, ',', ' ') . ' €';
                }),
            ChoiceField::new('tva', 'TVA')
                ->setChoices([
                    '5.5%' => '5.5',
                    '10%' => '10',
                    '20%' => '20'
                ])
                ->formatValue(function ($value, $entity) {
                return $value . ' %';
            }),
            AssociationField::new('category', 'Categorie associé')
        ];
    }
}

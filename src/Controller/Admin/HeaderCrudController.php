<?php

namespace App\Controller\Admin;

use App\Entity\Header;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class HeaderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Header::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Header')
            ->setEntityLabelInPlural('Headers')
            ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Titre'),
            TextareaField::new('content', 'Contenu'),
            TextField::new('buttonTitle', 'Label du bouton'),
            TextField::new('buttonLink', 'Url du bouton'),
            ImageField::new('illustration', 'Image')
                ->setHelp('Image de fond du header')
                ->setUploadDir('public/uploads')
                ->setBasePath('uploads')
                ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
                ->setRequired($pageName !== Crud::PAGE_EDIT),
        ];
    }

}

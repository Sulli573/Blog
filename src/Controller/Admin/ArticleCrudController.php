<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            // IdField::new('id'),
            TextField::new('titre'),
            TextEditorField::new('contenu'),
            DateField::new('createdAt'),
            AssociationField::new('categorie')
                                ->setFOrmTypeOption('choice_label','nom'),
            AssociationField::new('user')
                                ->setFOrmTypeOption('choice_label','email'),
            ImageField::new('image')->setBasePath('uploads')
                                    ->setUploadDir('public/uploads')
        ];
    }
    
}

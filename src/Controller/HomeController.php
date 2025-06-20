<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\NewArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    //opur images
    private $uploadsDir;
    
    public function __construct(string $uploadsDir) {
        $this->uploadsDir = $uploadsDir;
    }

    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $articles = $repo->findAll();
        $pagination = $paginator->paginate(
            $articles,
            $request->query->getInt('page',1),
           6,
        );
        return $this->render('home/index.html.twig', [
            'articles' => $pagination,
        ]);
    }
    //     #[Route('/article/new', name: 'app_article_new')]
    //     public function new(Request $request, EntityManagerInterface $em) {
    //         //instanciation de l'objet Article
    //         $article = new Article();
    //         //création du formulaire
    //         $form = $this->createForm(NewArticleType::class,$article);
    //         //mapper les propriété dans l'article avec les champs dans la base de données
    //         $form->handleRequest(($request));

    //         if($form->isSubmitted() and $form->isValid()) {
    //             $em->persist($article);
    //             $em->flush();
    //             return $this->redirectToRoute('app_home');
    //         }

    //         return $this->render('home/new.html.twig', [
    //             'form' =>$form->createView()
    //         ]);
    //     }
    //     #[Route('/article/edit/{id}', name:'app_article_edit')]
    //     //instancier un objet Article
    //     public function edit(Article $article, Request $request, EntityManagerInterface $em) {

    //          $form = $this->createForm(NewArticleType::class,$article);
    //         //mapper les propriété dans l'article avec les champs dans la base de données
    //         $form->handleRequest(($request));

    //         if($form->isSubmitted() and $form->isValid()) {
    //             $em->persist($article);
    //             $em->flush();
    //             return $this->redirectToRoute('app_home');
    //         }
    //         // la méthode render prend 2 paramètres:
    //         return $this->render('home/edit.html.twig',[
    //             'form' => $form->createView()
    //         ]);
    // }
    #[Route('/article/delete/{id}', name: 'app_article_delete')]
    #[IsGranted('ROLE_USER')]
    // Gràce à Security injectée dans la fonction il ets possible de récupérer ensuite l'utilisateur avec $security->getUser()
    public function delete(?Article $article, EntityManagerInterface $em, ?Security $security)
    {
       
        //récupérer l'utisateur connecté
        //    $user=$this->getUser();
        //    //récupérer l'user qui a créé l'article
        //    $article->getUser();
        // ou récupérer l'utisateur connecté
        // $security->getUser();
        // comparer les deux pour vérifier que l'utilisateur qui veut supprimer l'article est bien celui qui l'a créé
        // if ($this->getUser() == $article->getUser())
        if(!$article) {
             throw $this->createAccessDeniedException("Accès interdit : l'article n'existe pas.");
       
        }
        if ($this->getUser() !== $article->getUser()) {
            throw $this->createAccessDeniedException("Accès interdit : vous n'avez pas le droit de supprimer cet article.");
        }
        $em->remove($article);
        $em->flush();
        return $this->redirectToRoute('app_home');
    }
    //route avec parametre optionnel
    #[Route('/article/form/{id?}', name: 'app_article_form')]
    //SI vous n'avez pas le rôle admin, vous ne pourrez pas accéder à cette méthode
    public function form(?Article $article, Request $request, EntityManagerInterface $em)
    {
        if (!$article) {
            # code...
            $article = new Article();
        }
        //actionType permet de mettre le titre de la page "Ajouter un article" si pas de paramètre dans l'url et "Modifier un article" si un paramètre 
        $actionType = "Ajouter un article";
        //$title permet de mettre "New article" dans l'onglet du navigateur si on ajoute, et "Edit article" si on modifie
        $title = "New article";
        if ($article) {
            //Changer 
            $actionType = 'Modifier un article';
            $title = "Edit article";
            $form = $this->createForm(NewArticleType::class, $article);
            $form->handleRequest($request);
            //         //mapper les propriété dans l'article avec les champs dans la base de données
            //         $form->handleRequest(($request));
            if ($form->isSubmitted() and $form->isValid()) {
                $article->setUser($this->getUser());
                $image = $form->get('imageFile')->getData();
                $newName = uniqid().'.'.$image->guessExtension();
                $image->move($this->uploadsDir,$newName);
                $article->setImage($newName);
                $em->persist($article);
                $em->flush();
                return $this->redirectToRoute('app_home');
            }


            return $this->render('home/form.html.twig', [
                'form' => $form,
                'action' => $actionType,
                'title' => $title
            ]);
        }
    }
}

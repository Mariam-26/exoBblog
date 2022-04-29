<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticlesFormType;
use App\Repository\ArticlesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="app_blog")
     */
    public function index(ArticlesRepository $repo): Response
    {

        //Pour selectionner  les article en BDD nous devons absolument acces à la classe Repository correspondante ici c'est ArticleRepository

        //le repositery est la classe genere automatiquement lorsqu'il crer une entité Cette classe permet de faire uniquement des selection (requete SELECT * FROM articles)
        //Pour une selection à partir de notre classe Repository on instance $repo en parametre de la methode Cet objet va contenir des methodes qui permettent d'executer des requete
        //ex findAll() correspond à un SELECT juste mettre sa selectionne tous dans la base de donnee genre SELECT * FROM articles.
        //finBy() correspond à un SELECT avec des options

        $article = $repo->findBy(array(), array('createAt' => 'desc'));
        //equivalent SQL = SELECT * FROM articles ORDER BY createdAT desc fechAll()


        return $this->render('blog/index.html.twig', [
            'items' => $article,
            //je transmet dans la variable item(en twig) les element qui se trouvent dans $article les elements de la BDDs
        ]);
    }


    /* ceci est un commentaire */


    /*ceci est une annotation elle sera analyser par le navigateur */
    //POUR AFFICHER ID DES ARTICLES
    /**
     * @Route("/blog/show/{id}",  name="show")
     */

    public function show(Articles $article): Response
    {
        return $this->render('blog/show.html.twig', array(
            'article' => $article,
        ));
    }

    /* ajouter */

    /**
     * @Route("/blog/new",  name="new")
     * @Route("/blog/edit/{id}",  name="edit")
     */

    public function create(Articles $articleNew = null, Request $requete, EntityManagerInterface $manager): Response
    {
        if (!$articleNew) {
            $articleNew = new Articles();
        }

        $form = $this->createForm(ArticlesFormType::class, $articleNew);
        //ici on met en parametre la class d'apres laquelle on veut cree notre formulaire

        $form->handleRequest($requete);
        //on pioche la methode handleRequest() de la class Request du composant HTTPFoundation ça va nous permettent de recuperer chaque saisie faitre dans le formulaire $_POST['title'] etc.. et de les lier directement dans $articleNew

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$articleNew->getId()) //si l'id n'existe pas ce n'est pas une modification, c'est un ajout d'article

            {
                $articleNew->setCreateAt(new \DateTime()); //met a la date et a l'heure de la publication
            }

            $manager->persist($articleNew); //on met en mémoire les données récupérées dans  le formulaire
            $manager->flush(); // envoi des données dans la BDD
            return $this->redirectToRoute('show', [
                'id' => $articleNew->getId(), // on redirige vers la page show de l'article créé

            ]);
        }

        return $this->render('blog/new.html.twig', [
            'form' => $form->createView(), // ici on renvoie le formulaire avec tous les champs  requis pour l'isertion en BDD et on envoie vers la vue grâce au createView()

            'modeEdit' => $articleNew->getId()
        ]);
    }
}

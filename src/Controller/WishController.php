<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class WishController extends AbstractController
{
    const wishes = [
        ['id' => 1, 'libelle' => "Faire une descente en bobsleigh", 'class' => "fa-solid fa-egg"],
        ['id' => 2, 'libelle' => "Méditer", 'class' => "fa-regular fa-lightbulb"],
        ['id' => 3, 'libelle' => "Marcher sur des charbons ardents", 'class' => "fa-solid fa-fire"],
        ['id' => 4, 'libelle' => "Aller dans l'espace", 'class' => "fa-solid fa-rocket"]
    ];
    #[Route('/wish', name: 'app_wish')]
    public function index(WishRepository $wishRepository): Response
    {
        //$wishes2 = $wishRepository->findBy(['isPublished' => true], ['dateCreated' => 'DESC']);
        $wishes = $wishRepository->findAll();
        //A FAIRE : $wishes3 = $wishRepository->findPublishedWishesWithCategories();
        return $this->render('wish/wish.html.twig', ['wishes' => $wishes]);
    }

    #[Route('/wish/{id}', name: 'app_wish_detail', requirements: ['id' => '\d+'])]
    public function detail(WishRepository $wishRepository, int $id): Response
    {
        $wish = $wishRepository->find($id);
        //Se prémunir contre un id renseigné qui sort de la base
        if(!$wish){
            throw $this->createNotFoundException("Ce wish n'existe pas.");
        }
        return $this->render('wish/details.html.twig', ['wish' => $wish]);
    }

    #[Route('/wish/creer', name: 'app_wish_creer')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        //recuperer auteur
        $author = $this->getUser();
        $wish = new Wish();
        $wishForm = $this->createForm(WishType::class, $wish);
        $wishForm->handleRequest($request);

        if($wishForm->isSubmitted() && $wishForm->isValid()){
           //ajouter image et classe par défaut
            $wish->setUser($author);
            $wish->setUrl('default');
            $wish->setDateCreated(new \DateTime());
            $wish->setClass("fa-regular fa-star");
            $entityManager->persist($wish);
            $entityManager->flush();

            $this->addFlash('success','Un nouveau souhait a été enregistré');
            return $this->redirectToRoute('app_wish');
        }

        return $this->render('wish/creer-wish.html.twig', ['wish_form' => $wishForm]);
    }

    #[Route('wish/update/{id}', name:'app_wish_update', requirements: ['id'=>'\d+'])]
    public function update(Request $request, Wish $wish, EntityManagerInterface $entityManager):Response{
        $wishForm = $this->createForm(WishType::class, $wish);
        $wishForm->handleRequest($request);

        if($wishForm->isSubmitted() && $wishForm->isValid()){
            $entityManager->flush();

            $this->addFlash('success', "Le souhait {$wish->getTitle()} a été modifié");
            return $this->redirectToRoute('app_wish_detail', ['id'=>$wish->getId()]);
        }
        return $this->render('wish/creer-wish.html.twig', [
            'wish_form' => $wishForm,
            'wish' => $wish,
        ]);
    }

    #[Route('wish/delete/{id}', name:'app_wish_delete', requirements: ['id'=>'\d+'])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(Wish $wish, EntityManagerInterface $entityManager, Request $request):Response{

        //récupération du token ajouté dans un champ caché du formulaire (le bouton supprimer)
        //vérifier que l'utilisateur vient bien de ce bouton pour déclencher la suppression
        $token = $request->query->get('token');
        if($this->isCsrfTokenValid('wish_delete'.$wish->getId(), $token)){
            $entityManager->remove($wish);
            $entityManager->flush();

            $this->addFlash('success', 'Le souhait a été supprimé');
            return $this->redirectToRoute('app_wish');
        }
        $this->addFlash('danger', 'Action impossible');
        return $this->redirectToRoute('app_wish', ['id'=>$wish->getId()]);

    }

    //Fonction pour tester ajout catégorie au clic bouton
    #[Route('/wish/add/{id}', name: 'app_wish_add')]
    function ajouterCateg(int $id, EntityManagerInterface $entityManager){
        $category = $entityManager->getRepository(Category::class)->find(2);
        $wish = $entityManager->getRepository(Wish::class)->find($id);
        $wish->addCategory($category);
        $entityManager->persist($wish);
        $entityManager->flush();

        return $this->redirectToRoute('app_wish');
    }
}

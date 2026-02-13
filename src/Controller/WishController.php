<?php

namespace App\Controller;

use App\Repository\WishRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
        $wishes2 = $wishRepository->findBy([], ['dateCreated' => 'ASC']);
        $wishes = $wishRepository->findAll();
        return $this->render('wish/wish.html.twig', ['wishes' => $wishes2]);
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
}

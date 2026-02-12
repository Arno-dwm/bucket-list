<?php

namespace App\Controller;

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
    public function index(): Response
    {
        return $this->render('wish/wish.html.twig', ['wishes' => self::wishes]);
    }

    #[Route('/wish/{id}', name: 'app_wish_detail', requirements: ['id' => '\d+'])]
    public function detail($id): Response
    {
        return $this->render('wish/details.html.twig');
    }
}

<?php
//src/Controller/DeckController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Deck;
use App\Form\DeckType;

class DeckController extends AbstractController
{
    #[Route('/')]
    public function welcomeAction(Request $request): Response
    {
		// Create and init a deck object
		$deck = new Deck();
		$deck->setImageSize(Deck::CARD_SIZE_LARGE);
		
		// Create a deck creation form
		$form = $this->createForm(DeckType::class, $deck);
		$form->handleRequest($request);
		
		// Handle submitted forms
		if ($form->isSubmitted() && $form->isValid()) {
            $submittedDeck = $form->getData();

            // ... perform some action, such as saving the task to the database

            return $this->redirectToRoute('task_success');
        }
		
		// Render the form if not submitted
		return $this->render('deck/new.html.twig', [
            'form' => $form,
        ]);
    }
}

<?php
//src/Controller/DeckController.php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Deck;
use App\Form\DeckType;

class DeckController extends AbstractController
{
    #[Route('/', name: 'deck.new')]
    public function newDeckAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create and init a deck object
        $deck = new Deck();
        $deck->setImageSize(Deck::CARD_SIZE_LARGE);

        // Create a deck creation form
        $form = $this->createForm(DeckType::class, $deck);
        $form->handleRequest($request);

        // Handle submitted forms
        if ($form->isSubmitted() && $form->isValid()) {

            // Get deck information; the original deck object is also updated
            $formData = $form->getData();
            $cardSize = $formData->imageSize;

            // Generate a uid for the deck
            $uid = uniqid();

            // Persist to database
            // TODO
            // TODO use managerinterface

            // Create deck file creation job
            // TODO

            // Redirect to the 'submitted' page, passing UID and size through URL
            return $this->redirectToRoute('/deck/');
        }

        // Render the form if not submitted
        return $this->render('deck/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/deck/{deckId}')]
    public function deckDownload(int $deckId, Request $request): Response
    {
        // Render the 'submitted' message, with JS that downloads the file when ready
        return $this->render('deck/submitted.html.twig', [
            'deckId' => $deckId
        ]);
    }

    #[Route('/ajax/deck')]
    public function ajaxCreateDeckFile(){
        // Given a deck ID, retrieve the necessary info
        //TODO

        // Using deck info, start process to create deck file
        //TODO

        // Return an exit code to tell the caller whether the deck file is ready to be downloaded
        //TODO
        //TODO: write the ajax request & javascript on the submitted page; I don't understand it at all right now

    }
}

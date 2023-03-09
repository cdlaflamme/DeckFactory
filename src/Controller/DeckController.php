<?php
//src/Controller/DeckController.php

namespace App\Controller;

use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
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
            $size = $form->get('imageSize');

            // Generate uid for the deck
            $uid = uniqid();
            $deck->setUid($uid);

            // Persist deck info to database
            $entityManager->persist($deck);
            $entityManager->flush();

            // Start deck file creation job
            // TODO dispatch an event, which sets the job status when started / done

            // Redirect to the 'submitted' page, passing UID and size through URL
            return $this->redirectToRoute('deck.download', ['deckUid' => $uid]);
        }

        // Render the form if not submitted
        return $this->render('deck/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/deck/{deckUid}/', name: 'deck.download')]
    public function deckDownloadAction(string $deckUid, Request $request, DeckRepository $deckRepo): Response
    {
        //TODO query to find the deck object, for displaying info like name and maybe job status
        //TODO possibly display the url & back url so folks can catch mistakes

        // Retrieve the deck object to get relevant information
        $deck = $deckRepo->findOneBy(['uid' => $deckUid]);

        // Render the 'deck submitted' page, with JS that downloads the file when it's ready
        return $this->render('deck/download.html.twig', [
            'deck' => $deck
        ]);
    }

    #[Route('/_ajax/deck/{deckUid}', name: 'ajax.deck.status')]
    public function ajaxDeckStatus(){
        // Given a deck ID, retrieve the necessary info
        //TODO

        // Return an exit code to tell the caller whether the deck file is ready to be downloaded
        //TODO
        //TODO: write the ajax request & javascript on the submitted page; I don't understand it at all right now
    }
}

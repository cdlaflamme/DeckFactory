<?php
//src/Controller/DeckController.php

namespace App\Controller;

use App\Event\DeckCreatedEvent;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Deck;
use App\Form\DeckType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeckController extends AbstractController
{
    #[Route('/', name: 'deck.new')]
    public function newDeckAction(Request $request, EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher): Response
    {
        // Create and init a deck object
        $deck = new Deck();

        //TODO now that image size is not a mapped field I don't know how to set the default

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
            $deck->setJobStatus(Deck::JOB_UNSTARTED);
            $entityManager->persist($deck);
            $entityManager->flush();

            // Start deck file creation job
            $event = new DeckCreatedEvent($deck);
            $dispatcher->dispatch($event, DeckCreatedEvent::NAME);

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
        // Retrieve the deck object to get relevant information
        $deck = $deckRepo->findOneBy(['uid' => $deckUid]);

        // Render the 'deck submitted' page, with JS that downloads the file when it's ready
        return $this->render('deck/download.html.twig', [
            'deck' => $deck
        ]);
    }

    #[Route('/_ajax/deck/{deckUid}', name: 'ajax.deck.job_status')]
    public function ajaxDeckStatus(Request $request, string $deckUid, DeckRepository $deckRepo){

        // Fail for non-ajax requests
        if ( !($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) ) {
            return new JsonResponse(
                array(
                    'status' => 'Error',
                    'message' => 'Error'
                ),
                400
            );
        }

        // Get relevant deck
        $deck = $deckRepo->findOneBy(['uid' => $deckUid]);

        // Query table for job status, return that
        $status = $deck->getJobStatus();
        return new JsonResponse(
            array(
                'status' => 'OK',
                'message' => $status
            ),
            200
        );
    }
}

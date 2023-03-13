<?php
//src/Controller/DeckController.php

namespace App\Controller;

use App\Event\DeckCreatedEvent;
use App\Kernel;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
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

    protected string $deckDirectoryPath; // Path to generated deck files. Provided by services.yaml

    public function __construct(Kernel $kernel){
        $this->deckDirectoryPath = $kernel->getProjectDir().'/generated/decks/';
    }

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
            return $this->redirectToRoute('deck.status', ['deckUid' => $uid]);
        }

        // Render the form if not submitted
        return $this->render('deck/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/deck/{deckUid}/', name: 'deck.status')]
    public function deckStatusAction(string $deckUid, Request $request, DeckRepository $deckRepo): Response
    {
        // Retrieve the deck object to get relevant information
        $deck = $deckRepo->findOneBy(['uid' => $deckUid]);

        // Render the 'deck submitted' page, with JS that downloads the file when it's ready
        return $this->render('deck/download.html.twig', [
            'deck' => $deck
        ]);
    }

    #[Route('/deck/{deckUid}/download', name: 'deck.download')]
    public function deckDownloadAction(string $deckUid, DeckRepository $deckRepo): ?BinaryFileResponse {
        // Retrieve the deck object to get relevant information
        $deck = $deckRepo->findOneBy(['uid' => $deckUid]);
        $filePath = self::getDeckFilePath($deck);

        try {
            // Create a File object for assembly
            $fileObject = new File($filePath);
            // Return the file
            return $this->file($fileObject, $deck->getFinalFilename());
        }
        catch (\Exception $e) {
            return null;
        }
    }

    #[Route('/_ajax/deck/{deckUid}', name: 'ajax.deck.status')]
    public function ajaxDeckStatus(Request $request, string $deckUid, DeckRepository $deckRepo): JsonResponse {

        // Fail for non-ajax requests
        if ( !($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) ) {
            return new JsonResponse(
                array(
                    'jobStatus' => null
                ),
                400
            );
        }

        // Get relevant deck
        $deck = $deckRepo->findOneBy(['uid' => $deckUid]);

        // Query table for job status
        $status = $deck->getJobStatus();

        $downloadUrl = $deck->getLocalFilename();

        // Return job status
        return new JsonResponse(
            array(
                'jobStatus' => $status,
                'downloadUrl' => $downloadUrl
            ),
            200
        );
    }

    private function getDeckFilePath(Deck $deck): string {
        return $this->deckDirectoryPath . $deck->getLocalFilename();
    }
}

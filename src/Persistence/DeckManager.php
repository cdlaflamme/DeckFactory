<?php

namespace App\Persistence;

use App\Entity\Deck;
use App\Event\DeckCreatedEvent;
use App\Kernel;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class DeckManager {

    protected string $deckDirectoryPath; // Path to generated deck files. Provided by services.yaml

    protected EntityManagerInterface $entityManager;
    protected EventDispatcherInterface $eventDispatcher;
    protected DeckRepository $deckRepo;

    public function __construct(Kernel $kernel, EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher, DeckRepository $deckRepo){
        $this->deckDirectoryPath = $kernel->getProjectDir().'/generated/decks/';
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $dispatcher;
        $this->deckRepo = $deckRepo;
    }

    public function getDeckDirectoryPath(): string {
        return $this->deckDirectoryPath;
    }

    public function createDeck(Deck $deck, string $size){
        // Generate uid for the deck
        $uid = uniqid();
        $deck->setUid($uid);

        // Persist deck info to database
        $deck->setJobStatus(Deck::JOB_UNSTARTED);
        $this->entityManager->persist($deck);
        $this->entityManager->flush();

        // Start deck file creation job
        $event = new DeckCreatedEvent($deck, $size);
        $this->eventDispatcher->dispatch($event, DeckCreatedEvent::class);
    }

    public function getDeckFilePath(Deck $deck): string {
        return $this->deckDirectoryPath . $deck->getLocalFilename();
    }

    public function setDeckJobStatus(Deck $deck, int $status){
        $deck->setJobStatus($status);
        $this->entityManager->persist($deck);
        $this->entityManager->flush();
    }

}
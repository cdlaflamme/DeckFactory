<?php

namespace App\EventListener;

use App\Entity\Deck;
use App\Event\DeckCreatedEvent;
use App\Persistence\DeckManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class DeckCreatedListener {

    protected DeckManager $deckManager;

    public function __construct(DeckManager $deckManager){
        $this->deckManager = $deckManager;
    }

    #[AsEventListener]
    public function onDeckCreated(DeckCreatedEvent $event) : void {

        // Set-up
        $deck = $event->getDeck();
        $inUrl = $deck->getListUrl();
        $outPath = $this->deckManager->getDeckFilePath($deck);
        $this->deckManager->setDeckJobStatus($deck, Deck::JOB_STARTED);

        // Run file creation job
        // TODO handle size and custom cardback url
        $process = new Process(['python3', 'assets/python/tappedout_reader.py', $inUrl, $outPath]);
        $process->run();

        // Executes after the command finishes
        if (!$process->isSuccessful()) {
            $this->deckManager->setDeckJobStatus($deck, Deck::JOB_INCOMPLETE);
            throw new ProcessFailedException($process);
        }

        $this->deckManager->setDeckJobStatus($deck, Deck::JOB_SUCCESSFUL);
    }
}
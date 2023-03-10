<?php

namespace App\EventListener;

use App\Event\DeckCreatedEvent;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class DeckCreatedListener {

    #[AsEventListener]
    public function onDeckCreated(DeckCreatedEvent $event) : void {
        // Run process to create deck file, set job status when started & completed

        $deck = $event->getDeck();
        $inUrl = $deck->getListUrl();
        $outFileName = $deck->getLocalFilename();
        $outPath = 'generated/decks/'.$outFileName;

        // TODO set job status to "started"

        // Run file creation job
        $process = new Process(['python3', 'assets/python/pokemon_reader.py', $inUrl, $outPath]);
        $process->run();

        // Executes after the command finishes
        if (!$process->isSuccessful()) {
            // TODO set job status to "unsuccessful"
            throw new ProcessFailedException($process);
        }

        //TODO set job status to "complete"
    }
}
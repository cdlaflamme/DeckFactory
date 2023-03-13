<?php

namespace App\Event;

use App\Entity\Deck;
use Symfony\Contracts\EventDispatcher\Event;

class DeckCreatedEvent extends Event {

    protected Deck $deck;
    protected string $size;

    public function __construct(Deck $deck, string $size = Deck::CARD_SIZE_LARGE){
        $this->deck = $deck;
        $this->size = $size;
    }

    public function getDeck(): Deck{
        return $this->deck;
    }

    public function getSize(): string {
        return $this->size;
    }
}
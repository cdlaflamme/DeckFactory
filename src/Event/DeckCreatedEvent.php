<?php

namespace App\Event;

use App\Entity\Deck;
use Symfony\Contracts\EventDispatcher\Event;

class DeckCreatedEvent extends Event {

    public const NAME = 'deck.created';

    protected Deck $deck;

    public function __construct(Deck $deck){
        $this->deck = $deck;
    }

    public function getDeck(): Deck{
        return $this->deck;
    }
}
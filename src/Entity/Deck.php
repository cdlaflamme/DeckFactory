<?php
//src/Entity/Deck.php
namespace App\Entity;

class Deck
{
	// Constants for card sizes.
	public const CARD_SIZE_SMALL  = 0;
	public const CARD_SIZE_MEDIUM = 1;
	public const CARD_SIZE_LARGE  = 2;
	
	// Array of card names.
	protected $deckList;
	
	// URL to a Tappedout decklist.
	protected $deckListUrl;
	
	// Name of the deck. Used to create an output filename.
	protected $name;
	
	// Card back URL.
	protected $backUrl; 
	
	// Image size category to request from scyfall.
	protected $imageSize;
	
	//---------------------------------------
	
	public function getDeckList(): array {
		return $this->deckList;
	}
	
	public function getDeckListUrl(): string {
		return $this->deckListUrl;
	}
	
	public function getName(): string {
		return $this->name;
	}
	
	public function getBackUrl(): string {
		return $this->backUrl;
	}
	
	public function getimagesize(): int {
		return $this->imageSize;
	}
	
	//----------------------------------------
	
	public function setDeckList($deckList) {
		$this->deckList = $deckList;
	}
	
	public function setDeckListUrl($url) {
		$this->deckListUrl = filter_var($url, FILTER_SANITIZE_URL);
	}
	
	public function setName($name) {
		// Clean up the name (pls don't relative path me user-san)
		// from https://www.codexworld.com/how-to/clean-up-filename-string-to-make-url-and-filename-safe-using-php/
		$cleanName = pathinfo($name, PATHINFO_FILENAME); 
		// 1. Replaces all spaces with hyphens. 
		$cleanName = str_replace(' ', '-', $cleanName); 
		// 2. Removes special chars. 
		$cleanName = preg_replace('/[^A-Za-z0-9\-\_]/', '', $cleanName); 
		// 3. Replaces multiple hyphens with single one. 
		$cleanName = preg_replace('/-+/', '-', $cleanName); 
		 
		$this->name = $cleanName;
	}
	
	public function setBackUrl($url) {
		$this->backUrl = filter_var($url, FILTER_SANITIZE_URL);
	}
	
	public function setimagesize($size) {
		$this->imageSize = $size;
	}
}
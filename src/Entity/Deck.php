<?php

namespace App\Entity;

use App\Repository\DeckRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeckRepository::class)]
class Deck
{

    // Constants for card sizes.
    public const CARD_SIZE_SMALL  = 'small';
    public const CARD_SIZE_MEDIUM = 'medium';
    public const CARD_SIZE_LARGE  = 'large';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null; // Deck id in DB

    #[ORM\Column(length: 255)]
    private ?string $listUrl = null; // TappedOut url

    #[ORM\Column(length: 255)]
    private ?string $name = null; //name of deck; displayed on pages & used in filename

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $backUrl = null; // Custom card back url

    #[ORM\Column(length: 32)]
    private ?string $uid = null;

    #[ORM\Column]
    private ?int $jobStatus = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getListUrl(): ?string
    {
        return $this->listUrl;
    }

    public function setListUrl(string $listUrl): self
    {
        $this->listUrl = $listUrl;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBackUrl(): ?string
    {
        return $this->backUrl;
    }

    public function setBackUrl(?string $backUrl): self
    {
        $this->backUrl = $backUrl;

        return $this;
    }

    public function getLocalFilename(): ?string
    {
        return 'generated/decks/'.$this->uid.'.json';
    }

    public function getFinalFilename(): string
    {
        return $this->cleanFilename($this->getName()).'.json';
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function cleanFilename(string $name) : string
    {
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

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getJobStatus(): ?int
    {
        return $this->jobStatus;
    }

    public function setJobStatus(int $jobStatus): self
    {
        $this->jobStatus = $jobStatus;

        return $this;
    }
}

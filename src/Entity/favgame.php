<?php

namespace App\Entity;

use App\Repository\FavgameRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'favgames')]
class favgame
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'game_id')]
    #[Assert\NotBlank]
    private ?int $gameId = null;

    #[ORM\Column(name: 'name_game', length: 255)]
    #[Assert\NotBlank]
    private string $nameGame;

    #[ORM\Column(type: 'float')]
    #[Assert\Range(min: 0, max: 5)]
    private float $rate;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGameId(): ?int
    {
        return $this->gameId;
    }

    public function setGameId(?int $gameId): self
    {
        $this->gameId = $gameId;
        return $this;
    }

    public function getNameGame(): string
    {
        return $this->nameGame;
    }

    public function setNameGame(string $nameGame): self
    {
        $this->nameGame = $nameGame;
        return $this;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function setRate(float $rate): self
    {
        $this->rate = $rate;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }
}
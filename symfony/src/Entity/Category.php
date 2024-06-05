<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\CategoryRepository")]class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255)]
    private $nameEnglish;

    #[ORM\Column(type: "string", length: 255)]
    private $namePolish;

// Gettery i settery
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameEnglish(): ?string
    {
        return $this->nameEnglish;
    }

    public function setNameEnglish(string $nameEnglish): self
    {
        $this->nameEnglish = $nameEnglish;
        return $this;
    }

    public function getNamePolish(): ?string
    {
        return $this->namePolish;
    }

    public function setNamePolish(string $namePolish): self
    {
        $this->namePolish = $namePolish;
        return $this;
    }
}

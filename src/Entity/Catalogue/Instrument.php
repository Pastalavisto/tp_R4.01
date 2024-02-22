<?php

namespace App\Entity\Catalogue;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Instrument extends Article
{
    #[ORM\Column(length: 255,name: 'marque')]
    private ?string $marque = null;

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }
}
?>
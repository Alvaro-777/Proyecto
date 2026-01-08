<?php

namespace App\Entity;

use App\Repository\IARepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IARepository::class)]
class IA
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\Column]
    private ?int $costoCreditos = null;

    #[ORM\Column]
    private ?bool $accesibleAnonimos = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $urlExterna = null;

    #[ORM\Column(enumType: Entrada::class)]
    private ?Entrada $entradaPermitida = null;

    #[ORM\Column(enumType: Tipo::class)]
    private ?Tipo $tipo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getCostoCreditos(): ?int
    {
        return $this->costoCreditos;
    }

    public function setCostoCreditos(int $costoCreditos): static
    {
        $this->costoCreditos = $costoCreditos;

        return $this;
    }

    public function isAccesibleAnonimos(): ?bool
    {
        return $this->accesibleAnonimos;
    }

    public function setAccesibleAnonimos(bool $accesibleAnonimos): static
    {
        $this->accesibleAnonimos = $accesibleAnonimos;

        return $this;
    }

    public function getUrlExterna(): ?string
    {
        return $this->urlExterna;
    }

    public function setUrlExterna(?string $urlExterna): static
    {
        $this->urlExterna = $urlExterna;

        return $this;
    }

    public function getEntradaPermitida(): ?Entrada
    {
        return $this->entradaPermitida;
    }

    public function setEntradaPermitida(Entrada $entradaPermitida): static
    {
        $this->entradaPermitida = $entradaPermitida;

        return $this;
    }

    public function getTipo(): ?Tipo
    {
        return $this->tipo;
    }

    public function setTipo(Tipo $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }
}

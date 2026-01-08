<?php

namespace App\Entity;

use App\Repository\ArchivoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArchivoRepository::class)]
class Archivo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuarioId = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(nullable: true)]
    private ?int $peso = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $tipo = null;

    #[ORM\Column]
    private ?\DateTime $fechaSubida = null;

    public function __construct()
    {
        $this->fechaSubida = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuarioId(): ?Usuario
    {
        return $this->usuarioId;
    }

    public function setUsuarioId(?Usuario $usuarioId): static
    {
        $this->usuarioId = $usuarioId;

        return $this;
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

    public function getPeso(): ?int
    {
        return $this->peso;
    }

    public function setPeso(?int $peso): static
    {
        $this->peso = $peso;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getFechaSubida(): ?\DateTime
    {
        return $this->fechaSubida;
    }

    public function setFechaSubida(\DateTime $fechaSubida): static
    {
        $this->fechaSubida = $fechaSubida;

        return $this;
    }
}

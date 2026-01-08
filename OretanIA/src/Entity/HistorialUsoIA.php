<?php

namespace App\Entity;

use App\Repository\HistorialUsoIARepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistorialUsoIARepository::class)]
class HistorialUsoIA
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuarioId = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?IA $iaId = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Archivo $archivoId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $textoInput = null;

    #[ORM\Column]
    private ?\DateTime $fecha = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAnonimo = null;

    public function __construct()
    {
        $this->fecha = new \DateTime();
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

    public function getIaId(): ?IA
    {
        return $this->iaId;
    }

    public function setIaId(?IA $iaId): static
    {
        $this->iaId = $iaId;

        return $this;
    }

    public function getArchivoId(): ?Archivo
    {
        return $this->archivoId;
    }

    public function setArchivoId(?Archivo $archivoId): static
    {
        $this->archivoId = $archivoId;

        return $this;
    }

    public function getTextoInput(): ?string
    {
        return $this->textoInput;
    }

    public function setTextoInput(?string $textoInput): static
    {
        $this->textoInput = $textoInput;

        return $this;
    }

    public function getFecha(): ?\DateTime
    {
        return $this->fecha;
    }

    public function setFecha(\DateTime $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getIpAnonimo(): ?string
    {
        return $this->ipAnonimo;
    }

    public function setIpAnonimo(?string $ipAnonimo): static
    {
        $this->ipAnonimo = $ipAnonimo;

        return $this;
    }
}

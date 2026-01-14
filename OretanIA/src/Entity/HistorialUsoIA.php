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
    private ?Usuario $usuario = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?IA $ia = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Archivo $archivo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $textoInput = null;

    #[ORM\Column]
    private ?\DateTime $fecha = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ip = null;

    public function __construct()
    {
        $this->fecha = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getIa(): ?IA
    {
        return $this->ia;
    }

    public function setIa(?IA $ia): static
    {
        $this->ia = $ia;

        return $this;
    }

    public function getArchivo(): ?Archivo
    {
        return $this->archivo;
    }

    public function setArchivo(?Archivo $archivo): static
    {
        $this->archivo = $archivo;

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

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }
}

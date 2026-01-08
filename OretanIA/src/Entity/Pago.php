<?php

namespace App\Entity;

use App\Repository\PagoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PagoRepository::class)]
class Pago
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuarioId = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $cantidad = null;

    #[ORM\Column]
    private ?int $creditosObtenidos = null;

    #[ORM\Column]
    private ?\DateTime $fecha = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $metodo = null;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $valido = null;

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

    public function getCantidad(): ?string
    {
        return $this->cantidad;
    }

    public function setCantidad(string $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getCreditosObtenidos(): ?int
    {
        return $this->creditosObtenidos;
    }

    public function setCreditosObtenidos(int $creditosObtenidos): static
    {
        $this->creditosObtenidos = $creditosObtenidos;

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

    public function getMetodo(): ?string
    {
        return $this->metodo;
    }

    public function setMetodo(?string $metodo): static
    {
        $this->metodo = $metodo;

        return $this;
    }

    public function isValido(): ?bool
    {
        return $this->valido;
    }

    public function setValido(bool $valido): static
    {
        $this->valido = $valido;

        return $this;
    }
}

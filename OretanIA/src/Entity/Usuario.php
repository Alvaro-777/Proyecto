<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
class Usuario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $correo = null;

    #[ORM\Column(length: 100)]
    private ?string $contraseña = null;

    #[ORM\Column(length: 50)]
    private ?string $nombre = null;

    #[ORM\Column(length: 50)]
    private ?string $apellido = null;

    #[ORM\Column(nullable: true, options: ['default' => 50])]
    private ?int $creditos = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fechaRegistro = null;

    public function __construct()
    {
        $this->fechaRegistro = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(string $correo): static
    {
        $this->correo = $correo;

        return $this;
    }

    public function getContraseña(): ?string
    {
        return $this->contraseña;
    }

    public function setContraseña(string $contraseña): static
    {
        $this->contraseña = $contraseña;

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

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): static
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function getCreditos(): ?int
    {
        return $this->creditos;
    }

    public function setCreditos(?int $creditos): static
    {
        $this->creditos = $creditos;

        return $this;
    }

    public function getFechaRegistro(): ?\DateTime
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(\DateTime $fechaRegistro): static
    {
        $this->fechaRegistro = $fechaRegistro;

        return $this;
    }
}

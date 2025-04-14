<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ReservasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservasRepository::class)]
#[ApiResource]
class Reservas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $servicio = null;

    #[ORM\Column(length: 255)]
    private ?string $peluquero = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dia = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $hora = null;

    #[ORM\ManyToOne(inversedBy: 'reservas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuarios $usuario = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServicio(): ?string
    {
        return $this->servicio;
    }

    public function setServicio(string $servicio): static
    {
        $this->servicio = $servicio;

        return $this;
    }

    public function getPeluquero(): ?string
    {
        return $this->peluquero;
    }

    public function setPeluquero(string $peluquero): static
    {
        $this->peluquero = $peluquero;

        return $this;
    }

    public function getDia(): ?\DateTimeInterface
    {
        return $this->dia;
    }

    public function setDia(\DateTimeInterface $dia): static
    {
        $this->dia = $dia;

        return $this;
    }

    public function getHora(): ?\DateTimeInterface
    {
        return $this->hora;
    }

    public function setHora(\DateTimeInterface $hora): static
    {
        $this->hora = $hora;

        return $this;
    }

    public function getUsuario(): ?Usuarios
{
    return $this->usuario;
}

public function setUsuario(?Usuarios $usuario): static
{
    $this->usuario = $usuario;

    return $this;
}
}

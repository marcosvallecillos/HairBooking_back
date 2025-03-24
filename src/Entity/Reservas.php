<?php

namespace App\Entity;

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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apellidos = null;

    #[ORM\ManyToOne(inversedBy: 'reservas')]
    private ?Usuarios $usuario = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fecha_cita = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tipo_corte = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(?string $apellidos): static
    {
        $this->apellidos = $apellidos;

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

    public function getFechaCita(): ?\DateTimeInterface
    {
        return $this->fecha_cita;
    }

    public function setFechaCita(?\DateTimeInterface $fecha_cita): static
    {
        $this->fecha_cita = $fecha_cita;

        return $this;
    }

    public function getTipoCorte(): ?string
    {
        return $this->tipo_corte;
    }

    public function setTipoCorte(?string $tipo_corte): static
    {
        $this->tipo_corte = $tipo_corte;

        return $this;
    }
}

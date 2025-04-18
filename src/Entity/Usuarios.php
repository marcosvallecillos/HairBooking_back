<?php

namespace App\Entity;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\UsuariosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: UsuariosRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ],
    routePrefix: '/api'
)] 
class Usuarios
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apellidos = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;
    #[ORM\Column(length: 255)]
    private ?string $password = null;
    #[ORM\Column(nullable: true)]
    private ?int $telefono = null;

    /**
     * @var Collection<int, Reservas>
     */
  #[ORM\OneToMany(targetEntity: Reservas::class, mappedBy: 'usuario')]
    private Collection $reservas;

    /**
     * @var Collection<int, Productos>
     */
    #[ORM\ManyToMany(targetEntity: Productos::class)]
    #[ORM\JoinTable(name: 'usuarios_productos_favoritos')]
    private Collection $productosFavoritos;

    public function __construct()
    {
        $this->reservas = new ArrayCollection();
        $this->productosFavoritos = new ArrayCollection();
    }

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTelefono(): ?int
    {
        return $this->telefono;
    }

    public function setTelefono(?int $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }


    /**
     * @return Collection<int, Reservas>
     */
    public function getReservas(): Collection
    {
        return $this->reservas;
    }

    public function addReserva(Reservas $reserva): static
    {
        if (!$this->reservas->contains($reserva)) {
            $this->reservas->add($reserva);
            $reserva->setUsuario($this);
        }

        return $this;
    }

    public function removeReserva(Reservas $reserva): static
    {
        if ($this->reservas->removeElement($reserva)) {
            // set the owning side to null (unless already changed)
            if ($reserva->getUsuario() === $this) {
                $reserva->setUsuario(null);
            }
        }

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Collection<int, Productos>
     */
    public function getProductosFavoritos(): Collection
    {
        return $this->productosFavoritos;
    }

    public function addProductoFavorito(Productos $producto): static
    {
        if (!$this->productosFavoritos->contains($producto)) {
            $this->productosFavoritos->add($producto);
        }

        return $this;
    }

    public function removeProductoFavorito(Productos $producto): static
    {
        $this->productosFavoritos->removeElement($producto);

        return $this;
    }
}

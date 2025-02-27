<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use function Symfony\Component\String\s;

#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_SERVEUR') or is_granted('ROLE_BARMAN')"),
        new GetCollection(),
        new Patch(security: "is_granted('ROLE_SERVEUR') or is_granted('ROLE_BARMAN') and object.isNotPaid()"),
        new Post(security: "is_granted('ROLE_SERVEUR')"),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    forceEager: false
)]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ApiFilter(DateFilter::class, properties: ['date'])]
#[ApiFilter(SearchFilter::class, properties: ['status' => 'exact'])]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Drink>
     */
    #[ORM\ManyToMany(targetEntity: Drink::class)]
    #[Groups(['read', 'write'])]
    private Collection $drinks;

    #[ORM\Column]
    #[Groups(['read', 'write'])]
    private ?int $tableNumber = null;

    #[ORM\ManyToOne]
    #[Groups(['read', 'write'])]
    private ?User $waiter = null;

    #[ORM\ManyToOne]
    #[Groups(['read', 'write'])]
    private ?User $bartender = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read', 'write'])]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['read'])]
    private ?\DateTimeInterface $date = null;

    public function __construct()
    {
        $this->drinks = new ArrayCollection();
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Drink>
     */
    public function getDrinks(): Collection
    {
        return $this->drinks;
    }

    public function addDrink(Drink $drink): static
    {
        if (!$this->drinks->contains($drink)) {
            $this->drinks->add($drink);
        }

        return $this;
    }

    public function removeDrink(Drink $drink): static
    {
        $this->drinks->removeElement($drink);

        return $this;
    }

    public function getTableNumber(): ?int
    {
        return $this->tableNumber;
    }

    public function setTableNumber(int $tableNumber): static
    {
        $this->tableNumber = $tableNumber;

        return $this;
    }

    public function getWaiter(): ?User
    {
        return $this->waiter;
    }

    public function setWaiter(?User $waiter): static
    {
        $this->waiter = $waiter;

        return $this;
    }

    public function getBartender(): ?User
    {
        return $this->bartender;
    }

    public function setBartender(?User $bartender): static
    {
        $this->bartender = $bartender;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function isNotPaid(): bool
    {
        return $this->status !== 'payée';
    }
}

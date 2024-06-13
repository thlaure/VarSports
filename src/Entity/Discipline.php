<?php

namespace App\Entity;

use App\Constant\Constraint;
use App\Constant\Message;
use App\Repository\DisciplineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DisciplineRepository::class)]
class Discipline
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Length(max: 100, maxMessage: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Type(type: 'string', message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Regex(pattern: Constraint::REGEX_TITLE, message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    private ?string $label = null;

    /**
     * @var Collection<int, Club>
     */
    #[ORM\ManyToMany(targetEntity: Club::class, mappedBy: 'disciplines')]
    private Collection $clubs;

    public function __construct()
    {
        $this->clubs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, Club>
     */
    public function getClubs(): Collection
    {
        return $this->clubs;
    }

    public function addClub(Club $club): static
    {
        if (!$this->clubs->contains($club)) {
            $this->clubs->add($club);
            $club->addDiscipline($this);
        }

        return $this;
    }

    public function removeClub(Club $club): static
    {
        if ($this->clubs->removeElement($club)) {
            $club->removeDiscipline($this);
        }

        return $this;
    }
}

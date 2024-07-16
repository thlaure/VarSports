<?php

namespace App\Entity;

use App\Constant\Constraint;
use App\Constant\Message;
use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Length(max: 255, maxMessage: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Regex(pattern: Constraint::REGEX_COMMON, message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    private ?string $name = null;

    /**
     * @var Collection<int, PostalCode>
     */
    #[ORM\ManyToMany(targetEntity: PostalCode::class, mappedBy: 'city')]
    private Collection $postalCodes;

    #[ORM\ManyToOne(inversedBy: 'cities')]
    private ?Department $department = null;

    public function __construct()
    {
        $this->postalCodes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, PostalCode>
     */
    public function getPostalCodes(): Collection
    {
        return $this->postalCodes;
    }

    public function addPostalCode(PostalCode $postalCode): static
    {
        if (!$this->postalCodes->contains($postalCode)) {
            $this->postalCodes->add($postalCode);
            $postalCode->addCity($this);
        }

        return $this;
    }

    public function removePostalCode(PostalCode $postalCode): static
    {
        if ($this->postalCodes->removeElement($postalCode)) {
            $postalCode->removeCity($this);
        }

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

        return $this;
    }
}

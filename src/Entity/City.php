<?php

namespace App\Entity;

use App\Constant\Constraint;
use App\Constant\Message;
use App\Repository\CityRepository;
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

    #[ORM\Column(length: 5)]
    #[Assert\NotBlank(message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Length(max: 5, maxMessage: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Regex(pattern: Constraint::REGEX_POSTAL_CODE, message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    private ?string $postalCode = null;

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

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }
}

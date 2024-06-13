<?php

namespace App\Entity;

use App\Constant\Constraint;
use App\Constant\Message;
use App\Repository\ClubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClubRepository::class)]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Length(max: 255, maxMessage: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Regex(pattern: Constraint::REGEX_NAME, message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Length(max: 255, maxMessage: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Regex(pattern: Constraint::REGEX_COMMON, message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    private ?string $address = null;

    #[ORM\Column(length: 5)]
    #[Assert\NotBlank(message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Length(max: 5, maxMessage: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Regex(pattern: Constraint::REGEX_POSTAL_CODE, message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Length(max: 255, maxMessage: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Regex(pattern: Constraint::REGEX_COMMON, message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    private ?string $city = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(max: 10, maxMessage: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Regex(pattern: Constraint::REGEX_PHONE, message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Url(message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Regex(pattern: Constraint::REGEX_COMMON, message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    private ?string $logo = null;

    /**
     * @var Collection<int, Discipline>
     */
    #[ORM\ManyToMany(targetEntity: Discipline::class, inversedBy: 'clubs')]
    private Collection $disciplines;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 10000, maxMessage: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Regex(pattern: Constraint::REGEX_COMMON, message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    private ?string $description = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Length(max: 180, maxMessage: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Email(message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    #[Assert\Regex(pattern: Constraint::REGEX_EMAIL, message: Message::GENERIC_ENTITY_FIELD_ERROR)]
    private ?string $email = null;

    public function __construct()
    {
        $this->disciplines = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return Collection<int, Discipline>
     */
    public function getDisciplines(): Collection
    {
        return $this->disciplines;
    }

    public function addDiscipline(Discipline $discipline): static
    {
        if (!$this->disciplines->contains($discipline)) {
            $this->disciplines->add($discipline);
        }

        return $this;
    }

    public function removeDiscipline(Discipline $discipline): static
    {
        $this->disciplines->removeElement($discipline);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
}

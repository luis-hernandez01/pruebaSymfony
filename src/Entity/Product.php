<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{


    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('code', new Assert\NotBlank([
            'message' => "This field is mandatory it cannot be empty."
        ]));
        $metadata->addPropertyConstraint('code', new Assert\Length([
            'min' => 4,
           'max' => 10,
           'minMessage' => "The code must have a minimum of {{ limit }} characters",
           'maxMessage' => "The code must have a maximum of {{ limit }} characters"
        ]));
        $metadata->addPropertyConstraint('code', new Assert\Regex([
            'pattern' => '/[ ÑñÁáÉéÍíÓóÚú`+*#|{}<>.,-]/',
            'match' => false,
            'message' => 'Spaces and special characters are not accepted',
        ]));
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'code',
            'message' => 'This code is already registered.',
        ]));
        $metadata->addPropertyConstraint('name', new Assert\Length([
            'min' => 4,
            'minMessage' => "The name must have a minimum of {{ limit }} characters",
        ]));
        $metadata->addPropertyConstraint('name', new Assert\NotBlank([
            'message' => "This field is mandatory it cannot be empty."
        ]));
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'name',
            'message' => 'This name is already registered.',
        ]));
        $metadata->addPropertyConstraint('price', new Assert\NotBlank([
            'message' => "This field is mandatory it cannot be empty."
        ]));
        $metadata->addPropertyConstraint('price', new Assert\Range([
            'min' => 0,
            'notInRangeMessage' => 'Please enter {{ min }}cm a price greater than zero.',
        ]));
        $metadata->addPropertyConstraint('category', new Assert\NotBlank([
            'message' => "This field is mandatory it cannot be empty."
        ]));
        $metadata->addPropertyConstraint('description', new Assert\NotBlank([
            'message' => "This field is mandatory it cannot be empty."
        ]));
        $metadata->addPropertyConstraint('brand', new Assert\NotBlank([
            'message' => "This field is mandatory it cannot be empty."
        ]));
    }
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     *
     * @ORM\Column(type="string", length=10, unique=true)
     */
    private $code;

    /**
     *
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $brand;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="product")
     * @ORM\JoinColumn(nullable=true)
     */
    private $category;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;





    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category): void
    {
        $this->category = $category;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }




}

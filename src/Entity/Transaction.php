<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[ORM\Entity]
#[ORM\Table(name: 'budget_transaction')]
#[OA\Schema(
    title: 'Transaction',
    description: 'Transaction entity representing income or expense in a budget',
    type: 'object'
)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    #[OA\Property(description: 'Transaction ID', type: 'integer', format: 'int64', example: 1234567890)]
    private ?string $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    #[OA\Property(description: 'Transaction amount', type: 'number', format: 'float', example: 150.75)]
    private ?string $amount = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['income', 'expense'])]
    #[OA\Property(description: 'Transaction type', enum: ['income', 'expense'], example: 'income')]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[OA\Property(description: 'Transaction description (required)', example: 'Salary payment for January')]
    private ?string $comment = null;

    #[ORM\Column]
    #[OA\Property(description: 'Transaction creation date', format: 'date-time', example: '2025-01-15T14:30:00+00:00')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Budget::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Budget $budget = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount ? (float) $this->amount : null;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): static
    {
        $this->budget = $budget;

        return $this;
    }
}
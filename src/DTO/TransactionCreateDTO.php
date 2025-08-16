<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Transaction Creation',
    description: 'Data required to create a new transaction',
    type: 'object',
    required: ['amount', 'type', 'comment']
)]
class TransactionCreateDTO
{
    #[Assert\NotBlank(message: 'Amount is required')]
    #[Assert\PositiveOrZero(message: 'Amount must be zero or positive')]
    #[Assert\Type(type: 'numeric', message: 'Amount must be a valid number')]
    #[OA\Property(
        description: 'Transaction amount (always positive)',
        type: 'number',
        format: 'float',
        minimum: 0,
        example: 150.75
    )]
    public float $amount;

    #[Assert\NotBlank(message: 'Transaction type is required')]
    #[Assert\Choice(
        choices: ['income', 'expense'],
        message: 'Type must be either "income" or "expense"'
    )]
    #[OA\Property(
        description: 'Type of transaction',
        enum: ['income', 'expense'],
        example: 'income'
    )]
    public string $type;

    #[Assert\NotBlank(message: 'Comment is required')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Comment cannot exceed {{ limit }} characters'
    )]
    #[OA\Property(
        description: 'Description of the transaction (required)',
        maxLength: 255,
        example: 'Salary payment for January'
    )]
    public string $comment;
}
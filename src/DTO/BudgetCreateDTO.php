<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Budget Creation',
    description: 'Data required to create a new budget',
    type: 'object',
    required: ['name']
)]
class BudgetCreateDTO
{
    #[Assert\NotBlank(message: 'Budget name is required')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Budget name cannot exceed {{ limit }} characters'
    )]
    #[OA\Property(
        description: 'Budget name (required)',
        maxLength: 255,
        example: 'Holiday Savings'
    )]
    public string $name;

    #[OA\Property(
        description: 'Optional budget description',
        example: 'Money saved for summer vacation in Greece'
    )]
    public ?string $description = null;
}
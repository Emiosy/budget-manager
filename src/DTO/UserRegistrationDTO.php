<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'User Registration',
    description: 'Data required to register a new user account',
    type: 'object',
    required: ['email', 'password']
)]
class UserRegistrationDTO
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Please provide a valid email address')]
    #[OA\Property(
        description: 'User email address',
        format: 'email',
        example: 'user@example.com'
    )]
    public string $email;

    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(
        min: 6,
        minMessage: 'Password must be at least {{ limit }} characters long'
    )]
    #[OA\Property(
        description: 'User password (minimum 6 characters)',
        minLength: 6,
        example: 'secret123'
    )]
    public string $password;
}
<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'User Login',
    description: 'Credentials required to authenticate a user',
    type: 'object',
    required: ['email', 'password']
)]
class UserLoginDTO
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Please provide a valid email address')]
    #[OA\Property(
        description: 'User email address',
        format: 'email',
        example: 'test@example.com'
    )]
    public string $email;

    #[Assert\NotBlank(message: 'Password is required')]
    #[OA\Property(
        description: 'User password',
        example: 'password123'
    )]
    public string $password;
}
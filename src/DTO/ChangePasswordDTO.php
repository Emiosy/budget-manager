<?php

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    title: 'Change Password',
    description: 'Data required to change user password',
    type: 'object',
    required: ['currentPassword', 'newPassword']
)]
class ChangePasswordDTO
{
    #[Assert\NotBlank(message: 'Current password is required')]
    #[OA\Property(
        description: 'Current user password for verification',
        type: 'string',
        format: 'password',
        example: 'currentPassword123'
    )]
    public string $currentPassword;

    #[Assert\NotBlank(message: 'New password is required')]
    #[Assert\Length(min: 6, minMessage: 'Password must be at least 6 characters long')]
    #[OA\Property(
        description: 'New password (minimum 6 characters)',
        type: 'string',
        format: 'password',
        minLength: 6,
        example: 'newPassword123'
    )]
    public string $newPassword;

    #[Assert\NotBlank(message: 'Password confirmation is required')]
    #[Assert\EqualTo(propertyPath: 'newPassword', message: 'Password confirmation must match new password')]
    #[OA\Property(
        description: 'Confirmation of new password',
        type: 'string',
        format: 'password',
        example: 'newPassword123'
    )]
    public string $confirmPassword;
}
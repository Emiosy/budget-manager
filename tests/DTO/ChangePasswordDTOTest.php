<?php

namespace App\Tests\DTO;

use App\DTO\ChangePasswordDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangePasswordDTOTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidChangePasswordDTO(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'oldPassword123';
        $dto->newPassword = 'newPassword456';
        $dto->confirmPassword = 'newPassword456';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testCurrentPasswordIsRequired(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->newPassword = 'newPassword456';
        $dto->confirmPassword = 'newPassword456';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $currentPasswordViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'currentPassword'
        );
        $this->assertNotEmpty($currentPasswordViolations);
    }

    public function testNewPasswordIsRequired(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'oldPassword123';
        $dto->confirmPassword = 'newPassword456';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $newPasswordViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'newPassword'
        );
        $this->assertNotEmpty($newPasswordViolations);
    }

    public function testConfirmPasswordIsRequired(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'oldPassword123';
        $dto->newPassword = 'newPassword456';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $confirmPasswordViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'confirmPassword'
        );
        $this->assertNotEmpty($confirmPasswordViolations);
    }

    public function testNewPasswordTooShort(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'oldPassword123';
        $dto->newPassword = '12345'; // Only 5 characters
        $dto->confirmPassword = '12345';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $lengthViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'newPassword' && 
                strpos($v->getMessage(), 'at least 6 characters') !== false
        );
        $this->assertNotEmpty($lengthViolations);
    }

    public function testMinimumNewPasswordLength(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'oldPassword123';
        $dto->newPassword = '123456'; // Exactly 6 characters
        $dto->confirmPassword = '123456';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testPasswordConfirmationMismatch(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'oldPassword123';
        $dto->newPassword = 'newPassword456';
        $dto->confirmPassword = 'differentPassword789';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $matchViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'confirmPassword' && 
                strpos($v->getMessage(), 'Password confirmation must match') !== false
        );
        $this->assertNotEmpty($matchViolations);
    }

    public function testEmptyCurrentPassword(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = '';
        $dto->newPassword = 'newPassword456';
        $dto->confirmPassword = 'newPassword456';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $currentPasswordViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'currentPassword'
        );
        $this->assertNotEmpty($currentPasswordViolations);
    }

    public function testEmptyNewPassword(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'oldPassword123';
        $dto->newPassword = '';
        $dto->confirmPassword = '';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $newPasswordViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'newPassword'
        );
        $this->assertNotEmpty($newPasswordViolations);
    }

    public function testEmptyConfirmPassword(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'oldPassword123';
        $dto->newPassword = 'newPassword456';
        $dto->confirmPassword = '';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $confirmPasswordViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'confirmPassword'
        );
        $this->assertNotEmpty($confirmPasswordViolations);
    }

    public function testSameCurrentAndNewPassword(): void
    {
        // This should be valid from validation perspective
        // Business logic should handle preventing same passwords
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'samePassword123';
        $dto->newPassword = 'samePassword123';
        $dto->confirmPassword = 'samePassword123';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testSpecialCharactersInPasswords(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'C0mpl3x@P@ssw0rd!';
        $dto->newPassword = 'N3w#P@ssw0rd$%^&*';
        $dto->confirmPassword = 'N3w#P@ssw0rd$%^&*';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testUnicodeCharactersInPasswords(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'старыйпароль123';
        $dto->newPassword = 'новыйпароль456';
        $dto->confirmPassword = 'новыйпароль456';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testLongPasswords(): void
    {
        $longPassword = str_repeat('a', 100);
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'oldPassword123';
        $dto->newPassword = $longPassword;
        $dto->confirmPassword = $longPassword;

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testWhitespaceInPasswords(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'old password with spaces';
        $dto->newPassword = 'new password with spaces';
        $dto->confirmPassword = 'new password with spaces';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testMultipleValidationErrors(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = '';
        $dto->newPassword = '123'; // Too short
        $dto->confirmPassword = 'different';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(2, $violations->count());
        
        $propertyPaths = array_map(
            fn(ConstraintViolation $v) => $v->getPropertyPath(),
            iterator_to_array($violations)
        );
        
        $this->assertContains('currentPassword', $propertyPaths);
        $this->assertContains('newPassword', $propertyPaths);
        $this->assertContains('confirmPassword', $propertyPaths);
    }

    public function testCaseSensitivePasswordConfirmation(): void
    {
        $dto = new ChangePasswordDTO();
        $dto->currentPassword = 'oldPassword123';
        $dto->newPassword = 'NewPassword456';
        $dto->confirmPassword = 'newpassword456'; // Different case

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $matchViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'confirmPassword' && 
                strpos($v->getMessage(), 'Password confirmation must match') !== false
        );
        $this->assertNotEmpty($matchViolations);
    }
}
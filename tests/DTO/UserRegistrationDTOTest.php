<?php

namespace App\Tests\DTO;

use App\DTO\UserRegistrationDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserRegistrationDTOTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidUserRegistrationDTO(): void
    {
        $dto = new UserRegistrationDTO();
        $dto->email = 'test@example.com';
        $dto->password = 'password123';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testEmailIsRequired(): void
    {
        $dto = new UserRegistrationDTO();
        $dto->password = 'password123';

        $violations = $this->validator->validate($dto);

        $this->assertCount(1, $violations);
        $this->assertEquals('email', $violations[0]->getPropertyPath());
        $this->assertEquals('Email is required', $violations[0]->getMessage());
    }

    public function testPasswordIsRequired(): void
    {
        $dto = new UserRegistrationDTO();
        $dto->email = 'test@example.com';

        $violations = $this->validator->validate($dto);

        $this->assertCount(1, $violations);
        $this->assertEquals('password', $violations[0]->getPropertyPath());
        $this->assertEquals('Password is required', $violations[0]->getMessage());
    }

    public function testInvalidEmailFormat(): void
    {
        $dto = new UserRegistrationDTO();
        $dto->email = 'invalid-email';
        $dto->password = 'password123';

        $violations = $this->validator->validate($dto);

        $this->assertCount(1, $violations);
        $this->assertEquals('email', $violations[0]->getPropertyPath());
        $this->assertEquals('Please provide a valid email address', $violations[0]->getMessage());
    }

    public function testPasswordTooShort(): void
    {
        $dto = new UserRegistrationDTO();
        $dto->email = 'test@example.com';
        $dto->password = '12345'; // Only 5 characters

        $violations = $this->validator->validate($dto);

        $this->assertCount(1, $violations);
        $this->assertEquals('password', $violations[0]->getPropertyPath());
        $this->assertStringContainsString('Password must be at least 6 characters long', $violations[0]->getMessage());
    }

    public function testMinimumPasswordLength(): void
    {
        $dto = new UserRegistrationDTO();
        $dto->email = 'test@example.com';
        $dto->password = '123456'; // Exactly 6 characters

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testEmptyEmail(): void
    {
        $dto = new UserRegistrationDTO();
        $dto->email = '';
        $dto->password = 'password123';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $emailViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'email'
        );
        $this->assertNotEmpty($emailViolations);
    }

    public function testEmptyPassword(): void
    {
        $dto = new UserRegistrationDTO();
        $dto->email = 'test@example.com';
        $dto->password = '';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $passwordViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'password'
        );
        $this->assertNotEmpty($passwordViolations);
    }

    public function testValidEmailFormats(): void
    {
        $validEmails = [
            'test@example.com',
            'user.name@domain.co.uk',
            'firstname+lastname@company.org',
            'user123@test-domain.info'
        ];

        foreach ($validEmails as $email) {
            $dto = new UserRegistrationDTO();
            $dto->email = $email;
            $dto->password = 'password123';

            $violations = $this->validator->validate($dto);

            $this->assertCount(0, $violations, "Failed for email: {$email}");
        }
    }

    public function testInvalidEmailFormats(): void
    {
        $invalidEmails = [
            'plainaddress',
            '@missingusername.com',
            'username@.com',
            'username@com'
        ];

        foreach ($invalidEmails as $email) {
            $dto = new UserRegistrationDTO();
            $dto->email = $email;
            $dto->password = 'password123';

            $violations = $this->validator->validate($dto);

            $this->assertGreaterThan(0, $violations->count(), "Should fail for email: {$email}");
        }
    }

    public function testLongPassword(): void
    {
        $dto = new UserRegistrationDTO();
        $dto->email = 'test@example.com';
        $dto->password = str_repeat('a', 100); // Very long password

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testSpecialCharactersInPassword(): void
    {
        $dto = new UserRegistrationDTO();
        $dto->email = 'test@example.com';
        $dto->password = 'P@ssw0rd!#$%';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testMultipleValidationErrors(): void
    {
        $dto = new UserRegistrationDTO();
        $dto->email = 'invalid-email';
        $dto->password = '123'; // Too short

        $violations = $this->validator->validate($dto);

        $this->assertCount(2, $violations);
        
        $propertyPaths = array_map(
            fn(ConstraintViolation $v) => $v->getPropertyPath(),
            iterator_to_array($violations)
        );
        
        $this->assertContains('email', $propertyPaths);
        $this->assertContains('password', $propertyPaths);
    }
}
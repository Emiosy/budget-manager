<?php

namespace App\Tests\DTO;

use App\DTO\BudgetCreateDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BudgetCreateDTOTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidBudgetCreateDTO(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'Holiday Savings';
        $dto->description = 'Money saved for summer vacation in Greece';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testValidBudgetCreateDTOWithoutDescription(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'Emergency Fund';
        // description is null by default

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testNameIsRequired(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->description = 'Some description';

        $violations = $this->validator->validate($dto);

        $this->assertCount(1, $violations);
        $this->assertEquals('name', $violations[0]->getPropertyPath());
        $this->assertEquals('Budget name is required', $violations[0]->getMessage());
    }

    public function testEmptyName(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = '';
        $dto->description = 'Some description';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $nameViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'name'
        );
        $this->assertNotEmpty($nameViolations);
    }

    public function testWhitespaceOnlyName(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = '   ';
        $dto->description = 'Some description';

        $violations = $this->validator->validate($dto);

        // In Symfony, NotBlank constraint allows whitespace-only strings by default
        // Only empty string and null are considered blank
        // So this test should actually pass validation
        $this->assertCount(0, $violations);
    }

    public function testNameTooLong(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = str_repeat('a', 256); // 256 characters, limit is 255
        $dto->description = 'Some description';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $lengthViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'name' && 
                strpos($v->getMessage(), 'cannot exceed 255 characters') !== false
        );
        $this->assertNotEmpty($lengthViolations);
    }

    public function testMaximumNameLength(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = str_repeat('a', 255); // Exactly 255 characters
        $dto->description = 'Some description';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testShortName(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'A'; // Single character
        $dto->description = 'Some description';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testSpecialCharactersInName(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'Budget #1 - Car & House (2025)!';
        $dto->description = 'Budget with special characters';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testUnicodeCharactersInName(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'Бюджет на отпуск 2025';
        $dto->description = 'Unicode characters in name';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testEmojiInName(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = '🏖️ Holiday Budget 2025 ✈️';
        $dto->description = 'Budget with emoji';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testNullDescription(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'Test Budget';
        $dto->description = null;

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testEmptyDescription(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'Test Budget';
        $dto->description = '';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testWhitespaceOnlyDescription(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'Test Budget';
        $dto->description = '   ';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testLongDescription(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'Test Budget';
        $dto->description = str_repeat('This is a very long description. ', 50);

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testSpecialCharactersInDescription(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'Test Budget';
        $dto->description = 'Description with special chars: @#$%^&*()_+-=[]{}|;:,.<>?';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testMultilineDescription(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'Test Budget';
        $dto->description = "This is a multiline\ndescription with\ntab\tcharacters";

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testHtmlInDescription(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'Test Budget';
        $dto->description = '<p>HTML <strong>tags</strong> in description</p>';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testNumbersInName(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = '2025 Budget #123';
        $dto->description = 'Budget with numbers';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testOnlyNumbersInName(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = '12345';
        $dto->description = 'Numeric name';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testCommonBudgetNames(): void
    {
        $commonNames = [
            'Emergency Fund',
            'Holiday Savings',
            'Car Maintenance',
            'Home Improvement',
            'Education Fund',
            'Retirement Savings',
            'Wedding Budget',
            'Medical Expenses'
        ];

        foreach ($commonNames as $name) {
            $dto = new BudgetCreateDTO();
            $dto->name = $name;
            $dto->description = "Description for {$name}";

            $violations = $this->validator->validate($dto);

            $this->assertCount(0, $violations, "Failed for budget name: {$name}");
        }
    }

    public function testDefaultDescriptionValue(): void
    {
        $dto = new BudgetCreateDTO();
        
        $this->assertNull($dto->description);
    }

    public function testPropertyAssignment(): void
    {
        $dto = new BudgetCreateDTO();
        $dto->name = 'Test Budget';
        $dto->description = 'Test Description';

        $this->assertEquals('Test Budget', $dto->name);
        $this->assertEquals('Test Description', $dto->description);
    }
}
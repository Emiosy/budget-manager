<?php

namespace App\Tests\DTO;

use App\DTO\TransactionCreateDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransactionCreateDTOTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidTransactionCreateDTO(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 150.75;
        $dto->type = 'income';
        $dto->comment = 'Salary payment for January';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testAmountIsRequired(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->type = 'income';
        $dto->comment = 'Test comment';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $amountViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'amount'
        );
        $this->assertNotEmpty($amountViolations);
    }

    public function testTypeIsRequired(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 100.0;
        $dto->comment = 'Test comment';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $typeViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'type'
        );
        $this->assertNotEmpty($typeViolations);
    }

    public function testCommentIsRequired(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 100.0;
        $dto->type = 'income';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $commentViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'comment'
        );
        $this->assertNotEmpty($commentViolations);
    }

    public function testNegativeAmountIsInvalid(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = -50.0;
        $dto->type = 'income';
        $dto->comment = 'Test comment';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $amountViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'amount' && 
                strpos($v->getMessage(), 'zero or positive') !== false
        );
        $this->assertNotEmpty($amountViolations);
    }

    public function testZeroAmountIsValid(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 0.0;
        $dto->type = 'income';
        $dto->comment = 'Zero amount transaction';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testValidIncomeType(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 1000.0;
        $dto->type = 'income';
        $dto->comment = 'Salary';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testValidExpenseType(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 500.0;
        $dto->type = 'expense';
        $dto->comment = 'Groceries';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testInvalidType(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 100.0;
        $dto->type = 'invalid_type';
        $dto->comment = 'Test comment';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $typeViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'type' && 
                strpos($v->getMessage(), 'income') !== false && 
                strpos($v->getMessage(), 'expense') !== false
        );
        $this->assertNotEmpty($typeViolations);
    }

    public function testEmptyType(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 100.0;
        $dto->type = '';
        $dto->comment = 'Test comment';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $typeViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'type'
        );
        $this->assertNotEmpty($typeViolations);
    }

    public function testEmptyComment(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 100.0;
        $dto->type = 'income';
        $dto->comment = '';

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $commentViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'comment'
        );
        $this->assertNotEmpty($commentViolations);
    }

    public function testWhitespaceOnlyComment(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 100.0;
        $dto->type = 'income';
        $dto->comment = '   ';

        $violations = $this->validator->validate($dto);

        // In Symfony, NotBlank constraint allows whitespace-only strings by default
        // Only empty string and null are considered blank
        // So this test should actually pass validation
        $this->assertCount(0, $violations);
    }

    public function testCommentTooLong(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 100.0;
        $dto->type = 'income';
        $dto->comment = str_repeat('a', 256); // 256 characters, limit is 255

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, $violations->count());
        $commentViolations = array_filter(
            iterator_to_array($violations),
            fn(ConstraintViolation $v) => $v->getPropertyPath() === 'comment' && 
                strpos($v->getMessage(), 'cannot exceed 255 characters') !== false
        );
        $this->assertNotEmpty($commentViolations);
    }

    public function testMaximumCommentLength(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 100.0;
        $dto->type = 'income';
        $dto->comment = str_repeat('a', 255); // Exactly 255 characters

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testLargeAmount(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 999999.99;
        $dto->type = 'income';
        $dto->comment = 'Large amount transaction';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testSmallAmount(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 0.01;
        $dto->type = 'expense';
        $dto->comment = 'Small amount transaction';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testSpecialCharactersInComment(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 100.0;
        $dto->type = 'income';
        $dto->comment = 'Payment for café & restaurant - 25% tip included!';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testUnicodeCharactersInComment(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 100.0;
        $dto->type = 'expense';
        $dto->comment = 'Покупка продуктов в магазине';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testEmojiInComment(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 50.0;
        $dto->type = 'expense';
        $dto->comment = '🍕 Pizza dinner with friends 🎉';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testMultilineComment(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 200.0;
        $dto->type = 'income';
        $dto->comment = "Payment for project\nCompleted on time\nClient satisfied";

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testHtmlInComment(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 75.0;
        $dto->type = 'expense';
        $dto->comment = '<b>Important</b> purchase from <a href="#">store</a>';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testFloatAmountPrecision(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 123.456789; // High precision
        $dto->type = 'income';
        $dto->comment = 'Precise amount';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testIntegerAmount(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 100; // Integer instead of float
        $dto->type = 'expense';
        $dto->comment = 'Integer amount';

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testCommonTransactionTypes(): void
    {
        $testCases = [
            ['income', 'Salary payment'],
            ['income', 'Freelance work'],
            ['income', 'Investment return'],
            ['expense', 'Grocery shopping'],
            ['expense', 'Utility bills'],
            ['expense', 'Transportation costs']
        ];

        foreach ($testCases as [$type, $comment]) {
            $dto = new TransactionCreateDTO();
            $dto->amount = 100.0;
            $dto->type = $type;
            $dto->comment = $comment;

            $violations = $this->validator->validate($dto);

            $this->assertCount(0, $violations, "Failed for type: {$type}, comment: {$comment}");
        }
    }

    public function testMultipleValidationErrors(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = -100.0; // Invalid amount
        $dto->type = 'invalid'; // Invalid type
        $dto->comment = ''; // Empty comment

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(2, $violations->count());
        
        $propertyPaths = array_map(
            fn(ConstraintViolation $v) => $v->getPropertyPath(),
            iterator_to_array($violations)
        );
        
        $this->assertContains('amount', $propertyPaths);
        $this->assertContains('type', $propertyPaths);
        $this->assertContains('comment', $propertyPaths);
    }

    public function testPropertyAssignment(): void
    {
        $dto = new TransactionCreateDTO();
        $dto->amount = 250.75;
        $dto->type = 'expense';
        $dto->comment = 'Test transaction';

        $this->assertEquals(250.75, $dto->amount);
        $this->assertEquals('expense', $dto->type);
        $this->assertEquals('Test transaction', $dto->comment);
    }
}
<?php

namespace App\Tests\Entity;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    private Transaction $transaction;

    protected function setUp(): void
    {
        $this->transaction = new Transaction();
    }

    public function testTransactionCreation(): void
    {
        $this->assertInstanceOf(Transaction::class, $this->transaction);
        $this->assertNull($this->transaction->getId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->transaction->getCreatedAt());
        $this->assertNull($this->transaction->getAmount());
        $this->assertNull($this->transaction->getType());
        $this->assertNull($this->transaction->getComment());
        $this->assertNull($this->transaction->getBudget());
    }

    public function testAmountGetterAndSetter(): void
    {
        $amount = '150.75';
        
        $this->transaction->setAmount($amount);
        
        $this->assertEquals(150.75, $this->transaction->getAmount());
    }

    public function testAmountAsString(): void
    {
        $amount = '1000.50';
        
        $this->transaction->setAmount($amount);
        
        // Getter returns float, but amount is stored as string internally
        $this->assertEquals(1000.50, $this->transaction->getAmount());
        $this->assertIsFloat($this->transaction->getAmount());
    }

    public function testAmountWithZero(): void
    {
        $amount = '0.00';
        
        $this->transaction->setAmount($amount);
        
        $this->assertEquals(0.0, $this->transaction->getAmount());
    }

    public function testAmountWithInteger(): void
    {
        $amount = '500';
        
        $this->transaction->setAmount($amount);
        
        $this->assertEquals(500.0, $this->transaction->getAmount());
    }

    public function testTypeGetterAndSetter(): void
    {
        $type = 'income';
        
        $this->transaction->setType($type);
        
        $this->assertEquals($type, $this->transaction->getType());
    }

    public function testTypeIncome(): void
    {
        $this->transaction->setType('income');
        
        $this->assertEquals('income', $this->transaction->getType());
    }

    public function testTypeExpense(): void
    {
        $this->transaction->setType('expense');
        
        $this->assertEquals('expense', $this->transaction->getType());
    }

    public function testCommentGetterAndSetter(): void
    {
        $comment = 'Monthly salary payment';
        
        $this->transaction->setComment($comment);
        
        $this->assertEquals($comment, $this->transaction->getComment());
    }

    public function testCommentWithEmptyString(): void
    {
        $comment = '';
        
        $this->transaction->setComment($comment);
        
        $this->assertEquals($comment, $this->transaction->getComment());
    }

    public function testCommentWithSpecialCharacters(): void
    {
        $comment = 'Payment for café & restaurant - 25% tip included!';
        
        $this->transaction->setComment($comment);
        
        $this->assertEquals($comment, $this->transaction->getComment());
    }

    public function testCreatedAtGetterAndSetter(): void
    {
        $date = new \DateTimeImmutable('2025-01-15 14:30:00');
        
        $this->transaction->setCreatedAt($date);
        
        $this->assertEquals($date, $this->transaction->getCreatedAt());
    }

    public function testCreatedAtDefaultValue(): void
    {
        $now = new \DateTimeImmutable();
        $createdAt = $this->transaction->getCreatedAt();
        
        $this->assertInstanceOf(\DateTimeImmutable::class, $createdAt);
        // Check that the creation time is within the last second
        $this->assertLessThanOrEqual(1, $now->getTimestamp() - $createdAt->getTimestamp());
    }

    public function testBudgetGetterAndSetter(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        
        $budget = new Budget();
        $budget->setName('Test Budget');
        $budget->setUser($user);
        
        $this->transaction->setBudget($budget);
        
        $this->assertEquals($budget, $this->transaction->getBudget());
    }

    public function testBudgetCanBeNull(): void
    {
        $this->transaction->setBudget(null);
        
        $this->assertNull($this->transaction->getBudget());
    }

    public function testBudgetRelationship(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        
        $budget = new Budget();
        $budget->setName('Test Budget');
        $budget->setUser($user);
        
        $this->transaction->setBudget($budget);
        
        // Test that the relationship is properly established
        $this->assertEquals($budget, $this->transaction->getBudget());
        
        // When we add the transaction to the budget, it should contain our transaction
        $budget->addTransaction($this->transaction);
        $this->assertTrue($budget->getTransactions()->contains($this->transaction));
    }

    public function testFluentInterface(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        
        $budget = new Budget();
        $budget->setName('Test Budget');
        $budget->setUser($user);
        
        $date = new \DateTimeImmutable('2025-01-15 14:30:00');
        
        $result = $this->transaction
            ->setAmount('500.75')
            ->setType('income')
            ->setComment('Freelance payment')
            ->setCreatedAt($date)
            ->setBudget($budget);
        
        $this->assertSame($this->transaction, $result);
        $this->assertEquals(500.75, $this->transaction->getAmount());
        $this->assertEquals('income', $this->transaction->getType());
        $this->assertEquals('Freelance payment', $this->transaction->getComment());
        $this->assertEquals($date, $this->transaction->getCreatedAt());
        $this->assertEquals($budget, $this->transaction->getBudget());
    }

    public function testCompleteTransaction(): void
    {
        $user = new User();
        $user->setEmail('john@example.com');
        
        $budget = new Budget();
        $budget->setName('Holiday Savings');
        $budget->setDescription('Money for summer vacation');
        $budget->setUser($user);
        
        $this->transaction
            ->setAmount('1250.00')
            ->setType('income')
            ->setComment('Bonus payment from company')
            ->setBudget($budget);
        
        $budget->addTransaction($this->transaction);
        
        // Verify all properties are set correctly
        $this->assertEquals(1250.0, $this->transaction->getAmount());
        $this->assertEquals('income', $this->transaction->getType());
        $this->assertEquals('Bonus payment from company', $this->transaction->getComment());
        $this->assertEquals($budget, $this->transaction->getBudget());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->transaction->getCreatedAt());
        
        // Verify the budget relationship
        $this->assertTrue($budget->getTransactions()->contains($this->transaction));
        $this->assertEquals(1250.0, $budget->getBalance());
    }

    public function testTransactionInBudgetBalance(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        
        $budget = new Budget();
        $budget->setName('Test Budget');
        $budget->setUser($user);
        
        $income = new Transaction();
        $income->setAmount('1000.00')
               ->setType('income')
               ->setComment('Salary')
               ->setBudget($budget);
        
        $expense = new Transaction();
        $expense->setAmount('300.50')
                ->setType('expense')
                ->setComment('Groceries')
                ->setBudget($budget);
        
        $budget->addTransaction($income);
        $budget->addTransaction($expense);
        
        // Test that transactions affect budget balance correctly
        $this->assertEquals(699.50, $budget->getBalance());
    }

    public function testLargeAmount(): void
    {
        $amount = '999999.99';
        
        $this->transaction->setAmount($amount);
        
        $this->assertEquals(999999.99, $this->transaction->getAmount());
    }

    public function testAmountPrecision(): void
    {
        $amount = '123.45';
        
        $this->transaction->setAmount($amount);
        
        $this->assertEquals(123.45, $this->transaction->getAmount());
        $this->assertNotEquals(123.4, $this->transaction->getAmount());
        $this->assertNotEquals(123.46, $this->transaction->getAmount());
    }
}
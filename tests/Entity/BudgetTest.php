<?php

namespace App\Tests\Entity;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class BudgetTest extends TestCase
{
    private Budget $budget;

    protected function setUp(): void
    {
        $this->budget = new Budget();
    }

    public function testBudgetCreation(): void
    {
        $this->assertInstanceOf(Budget::class, $this->budget);
        $this->assertNotNull($this->budget->getId());
        $this->assertTrue(Uuid::isValid($this->budget->getId()));
        $this->assertEmpty($this->budget->getTransactions());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->budget->getCreatedAt());
    }

    public function testNameGetterAndSetter(): void
    {
        $name = 'Holiday Savings';
        
        $this->budget->setName($name);
        
        $this->assertEquals($name, $this->budget->getName());
    }

    public function testDescriptionGetterAndSetter(): void
    {
        $description = 'Money saved for summer vacation in Greece';
        
        $this->budget->setDescription($description);
        
        $this->assertEquals($description, $this->budget->getDescription());
    }

    public function testDescriptionCanBeNull(): void
    {
        $this->budget->setDescription(null);
        
        $this->assertNull($this->budget->getDescription());
    }

    public function testUserGetterAndSetter(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        
        $this->budget->setUser($user);
        
        $this->assertEquals($user, $this->budget->getUser());
    }

    public function testUserCanBeNull(): void
    {
        $this->budget->setUser(null);
        
        $this->assertNull($this->budget->getUser());
    }

    public function testCreatedAtGetterAndSetter(): void
    {
        $date = new \DateTimeImmutable('2025-01-15 10:30:00');
        
        $this->budget->setCreatedAt($date);
        
        $this->assertEquals($date, $this->budget->getCreatedAt());
    }

    public function testTransactionCollection(): void
    {
        $transaction1 = new Transaction();
        $transaction1->setType('income');
        $transaction1->setAmount('500.00');
        $transaction1->setComment('Salary');
        
        $transaction2 = new Transaction();
        $transaction2->setType('expense');
        $transaction2->setAmount('100.00');
        $transaction2->setComment('Groceries');
        
        $this->assertEquals(0, $this->budget->getTransactions()->count());
        
        $this->budget->addTransaction($transaction1);
        $this->assertEquals(1, $this->budget->getTransactions()->count());
        $this->assertTrue($this->budget->getTransactions()->contains($transaction1));
        $this->assertEquals($this->budget, $transaction1->getBudget());
        
        $this->budget->addTransaction($transaction2);
        $this->assertEquals(2, $this->budget->getTransactions()->count());
        $this->assertTrue($this->budget->getTransactions()->contains($transaction2));
        
        // Adding the same transaction twice should not duplicate it
        $this->budget->addTransaction($transaction1);
        $this->assertEquals(2, $this->budget->getTransactions()->count());
    }

    public function testRemoveTransaction(): void
    {
        $transaction = new Transaction();
        $transaction->setType('income');
        $transaction->setAmount('500.00');
        $transaction->setComment('Salary');
        
        $this->budget->addTransaction($transaction);
        $this->assertEquals(1, $this->budget->getTransactions()->count());
        $this->assertEquals($this->budget, $transaction->getBudget());
        
        $this->budget->removeTransaction($transaction);
        $this->assertEquals(0, $this->budget->getTransactions()->count());
        $this->assertFalse($this->budget->getTransactions()->contains($transaction));
        $this->assertNull($transaction->getBudget());
    }

    public function testRemoveNonExistentTransaction(): void
    {
        $transaction1 = new Transaction();
        $transaction1->setType('income');
        $transaction1->setAmount('500.00');
        $transaction1->setComment('Salary');
        
        $transaction2 = new Transaction();
        $transaction2->setType('expense');
        $transaction2->setAmount('100.00');
        $transaction2->setComment('Groceries');
        
        $this->budget->addTransaction($transaction1);
        $this->assertEquals(1, $this->budget->getTransactions()->count());
        
        // Removing a transaction that doesn't exist should not affect the collection
        $this->budget->removeTransaction($transaction2);
        $this->assertEquals(1, $this->budget->getTransactions()->count());
        $this->assertTrue($this->budget->getTransactions()->contains($transaction1));
    }

    public function testGetBalanceWithNoTransactions(): void
    {
        $balance = $this->budget->getBalance();
        
        $this->assertEquals(0.0, $balance);
    }

    public function testGetBalanceWithIncomeOnly(): void
    {
        $transaction1 = new Transaction();
        $transaction1->setType('income');
        $transaction1->setAmount('500.00');
        $transaction1->setComment('Salary');
        
        $transaction2 = new Transaction();
        $transaction2->setType('income');
        $transaction2->setAmount('250.50');
        $transaction2->setComment('Bonus');
        
        $this->budget->addTransaction($transaction1);
        $this->budget->addTransaction($transaction2);
        
        $balance = $this->budget->getBalance();
        
        $this->assertEquals(750.50, $balance);
    }

    public function testGetBalanceWithExpenseOnly(): void
    {
        $transaction1 = new Transaction();
        $transaction1->setType('expense');
        $transaction1->setAmount('100.00');
        $transaction1->setComment('Groceries');
        
        $transaction2 = new Transaction();
        $transaction2->setType('expense');
        $transaction2->setAmount('50.25');
        $transaction2->setComment('Gas');
        
        $this->budget->addTransaction($transaction1);
        $this->budget->addTransaction($transaction2);
        
        $balance = $this->budget->getBalance();
        
        $this->assertEquals(-150.25, $balance);
    }

    public function testGetBalanceWithMixedTransactions(): void
    {
        $income1 = new Transaction();
        $income1->setType('income');
        $income1->setAmount('1000.00');
        $income1->setComment('Salary');
        
        $income2 = new Transaction();
        $income2->setType('income');
        $income2->setAmount('500.00');
        $income2->setComment('Bonus');
        
        $expense1 = new Transaction();
        $expense1->setType('expense');
        $expense1->setAmount('300.00');
        $expense1->setComment('Rent');
        
        $expense2 = new Transaction();
        $expense2->setType('expense');
        $expense2->setAmount('150.75');
        $expense2->setComment('Utilities');
        
        $this->budget->addTransaction($income1);
        $this->budget->addTransaction($expense1);
        $this->budget->addTransaction($income2);
        $this->budget->addTransaction($expense2);
        
        $balance = $this->budget->getBalance();
        
        // 1000 + 500 - 300 - 150.75 = 1049.25
        $this->assertEquals(1049.25, $balance);
    }

    public function testFluentInterface(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        
        $date = new \DateTimeImmutable('2025-01-15 10:30:00');
        
        $result = $this->budget
            ->setName('Test Budget')
            ->setDescription('Test Description')
            ->setUser($user)
            ->setCreatedAt($date);
        
        $this->assertSame($this->budget, $result);
        $this->assertEquals('Test Budget', $this->budget->getName());
        $this->assertEquals('Test Description', $this->budget->getDescription());
        $this->assertEquals($user, $this->budget->getUser());
        $this->assertEquals($date, $this->budget->getCreatedAt());
    }

    public function testBalanceCalculationWithStringAmounts(): void
    {
        // Test that string amounts are properly converted to floats
        $income = new Transaction();
        $income->setType('income');
        $income->setAmount('1000.50');
        $income->setComment('Salary');
        
        $expense = new Transaction();
        $expense->setType('expense');
        $expense->setAmount('250.25');
        $expense->setComment('Shopping');
        
        $this->budget->addTransaction($income);
        $this->budget->addTransaction($expense);
        
        $balance = $this->budget->getBalance();
        
        $this->assertEquals(750.25, $balance);
    }
}
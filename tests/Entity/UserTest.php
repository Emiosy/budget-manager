<?php

namespace App\Tests\Entity;

use App\Entity\Budget;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testUserCreation(): void
    {
        $this->assertInstanceOf(User::class, $this->user);
        $this->assertNotNull($this->user->getId());
        $this->assertTrue(Uuid::isValid($this->user->getId()));
        $this->assertEmpty($this->user->getBudgets());
        $this->assertTrue($this->user->isActive());
        $this->assertEquals(['ROLE_USER'], $this->user->getRoles());
    }

    public function testEmailGetterAndSetter(): void
    {
        $email = 'test@example.com';
        
        $this->user->setEmail($email);
        
        $this->assertEquals($email, $this->user->getEmail());
        $this->assertEquals($email, $this->user->getUserIdentifier());
    }

    public function testPasswordGetterAndSetter(): void
    {
        $password = 'hashed_password_123';
        
        $this->user->setPassword($password);
        
        $this->assertEquals($password, $this->user->getPassword());
    }

    public function testRolesGetterAndSetter(): void
    {
        $roles = ['ROLE_ADMIN', 'ROLE_USER'];
        
        $this->user->setRoles($roles);
        
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $this->user->getRoles());
    }

    public function testRolesAlwaysIncludeRoleUser(): void
    {
        $roles = ['ROLE_ADMIN'];
        
        $this->user->setRoles($roles);
        
        $result = $this->user->getRoles();
        $this->assertContains('ROLE_USER', $result);
        $this->assertContains('ROLE_ADMIN', $result);
    }

    public function testRolesDuplicatesAreRemoved(): void
    {
        $roles = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_USER'];
        
        $this->user->setRoles($roles);
        
        $result = $this->user->getRoles();
        $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN'], array_values($result));
    }

    public function testIsActiveGetterAndSetter(): void
    {
        $this->assertTrue($this->user->isActive());
        
        $this->user->setIsActive(false);
        $this->assertFalse($this->user->isActive());
        
        $this->user->setIsActive(true);
        $this->assertTrue($this->user->isActive());
    }

    public function testBudgetCollection(): void
    {
        $budget1 = new Budget();
        $budget1->setName('Budget 1');
        
        $budget2 = new Budget();
        $budget2->setName('Budget 2');
        
        $this->assertEquals(0, $this->user->getBudgets()->count());
        
        $this->user->addBudget($budget1);
        $this->assertEquals(1, $this->user->getBudgets()->count());
        $this->assertTrue($this->user->getBudgets()->contains($budget1));
        $this->assertEquals($this->user, $budget1->getUser());
        
        $this->user->addBudget($budget2);
        $this->assertEquals(2, $this->user->getBudgets()->count());
        $this->assertTrue($this->user->getBudgets()->contains($budget2));
        
        // Adding the same budget twice should not duplicate it
        $this->user->addBudget($budget1);
        $this->assertEquals(2, $this->user->getBudgets()->count());
    }

    public function testRemoveBudget(): void
    {
        $budget = new Budget();
        $budget->setName('Test Budget');
        
        $this->user->addBudget($budget);
        $this->assertEquals(1, $this->user->getBudgets()->count());
        $this->assertEquals($this->user, $budget->getUser());
        
        $this->user->removeBudget($budget);
        $this->assertEquals(0, $this->user->getBudgets()->count());
        $this->assertFalse($this->user->getBudgets()->contains($budget));
        $this->assertNull($budget->getUser());
    }

    public function testRemoveNonExistentBudget(): void
    {
        $budget1 = new Budget();
        $budget1->setName('Budget 1');
        
        $budget2 = new Budget();
        $budget2->setName('Budget 2');
        
        $this->user->addBudget($budget1);
        $this->assertEquals(1, $this->user->getBudgets()->count());
        
        // Removing a budget that doesn't exist should not affect the collection
        $this->user->removeBudget($budget2);
        $this->assertEquals(1, $this->user->getBudgets()->count());
        $this->assertTrue($this->user->getBudgets()->contains($budget1));
    }

    public function testEraseCredentials(): void
    {
        // This method is deprecated but should not throw an error
        $this->user->eraseCredentials();
        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    public function testFluentInterface(): void
    {
        $result = $this->user
            ->setEmail('test@example.com')
            ->setPassword('password')
            ->setIsActive(false)
            ->setRoles(['ROLE_ADMIN']);
        
        $this->assertSame($this->user, $result);
        $this->assertEquals('test@example.com', $this->user->getEmail());
        $this->assertEquals('password', $this->user->getPassword());
        $this->assertFalse($this->user->isActive());
        $this->assertContains('ROLE_ADMIN', $this->user->getRoles());
    }
}
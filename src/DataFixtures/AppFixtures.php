<?php

namespace App\DataFixtures;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setIsActive(true);

        $manager->persist($user);

        $budgetNames = [
            'Oszczędności wakacyjne' => 'Pieniądze na wymarzone wakacje w Grecji',
            'Fundusz awaryjny' => 'Środki na nieprzewidziane wydatki',
            'Nowy samochód' => 'Oszczędności na wymianę starego auta'
        ];

        $transactionTemplates = [
            'income' => [
                'Wynagrodzenie',
                'Premia roczna',
                'Zwrot podatku',
                'Sprzedaż niepotrzebnych rzeczy',
                'Dodatek świąteczny',
                'Freelance - projekt webowy',
                'Dywidenda z akcji',
                'Zwrot za leczenie'
            ],
            'expense' => [
                'Zakupy spożywcze',
                'Paliwo do samochodu',
                'Rachunki za media',
                'Abonament telefoniczny',
                'Ubezpieczenie samochodu',
                'Wizyta u lekarza',
                'Kolacja w restauracji',
                'Zakup książek',
                'Kino z rodziną',
                'Remont mieszkania',
                'Prezent urodzinowy',
                'Subskrypcja Netflix'
            ]
        ];

        foreach ($budgetNames as $budgetName => $description) {
            $budget = new Budget();
            $budget->setName($budgetName);
            $budget->setDescription($description);
            $budget->setUser($user);
            $budget->setCreatedAt(new \DateTimeImmutable('-' . rand(30, 180) . ' days'));

            $manager->persist($budget);

            for ($i = 0; $i < 5; $i++) {
                $transaction = new Transaction();
                
                $type = rand(0, 1) ? 'income' : 'expense';
                $transaction->setType($type);
                
                $template = $transactionTemplates[$type];
                $comment = $template[array_rand($template)];
                $transaction->setComment($comment);
                
                $amount = match($type) {
                    'income' => rand(50000, 500000) / 100,
                    'expense' => rand(2000, 150000) / 100,
                };
                $transaction->setAmount((string) $amount);
                
                $transaction->setBudget($budget);
                $transaction->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 90) . ' days'));

                $manager->persist($transaction);
            }
        }

        $manager->flush();
    }
}

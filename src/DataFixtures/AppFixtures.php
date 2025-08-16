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
        $users = [
            [
                'email' => 'test@example.com',
                'password' => 'password123',
                'budgets' => [
                    'Oszczędności wakacyjne' => 'Pieniądze na wymarzone wakacje w Grecji',
                    'Fundusz awaryjny' => 'Środki na nieprzewidziane wydatki',
                    'Nowy samochód' => 'Oszczędności na wymianę starego auta'
                ]
            ],
            [
                'email' => 'anna.kowalska@example.com',
                'password' => 'password456',
                'budgets' => [
                    'Mieszkanie' => 'Oszczędności na pierwsze mieszkanie',
                    'Edukacja' => 'Fundusze na kursy i szkolenia',
                    'Hobby - fotografia' => 'Sprzęt fotograficzny i wyjazdy'
                ]
            ],
            [
                'email' => 'jan.nowak@example.com',
                'password' => 'password789',
                'budgets' => [
                    'Remont domu' => 'Modernizacja kuchni i łazienki',
                    'Dzieci - edukacja' => 'Opłaty szkolne i dodatkowe zajęcia',
                    'Emerytura' => 'Długoterminowe oszczędności emerytalne',
                    'Wakacje rodzinne' => 'Wyjazd z całą rodziną nad morze'
                ]
            ]
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

        foreach ($users as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $userData['password']));
            $user->setIsActive(true);

            $manager->persist($user);

            foreach ($userData['budgets'] as $budgetName => $description) {
                $budget = new Budget();
                $budget->setName($budgetName);
                $budget->setDescription($description);
                $budget->setUser($user);
                $budget->setCreatedAt(new \DateTimeImmutable('-' . rand(30, 180) . ' days'));

                $manager->persist($budget);

                $transactionCount = rand(3, 8);
                for ($i = 0; $i < $transactionCount; $i++) {
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
        }

        $manager->flush();
    }
}

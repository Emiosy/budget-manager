<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $budgets = $user->getBudgets();
        
        $totalBalance = 0;
        $totalIncomes = 0;
        $totalExpenses = 0;
        
        foreach ($budgets as $budget) {
            $balance = $budget->getBalance();
            $totalBalance += $balance;
            
            foreach ($budget->getTransactions() as $transaction) {
                if ($transaction->getType() === 'income') {
                    $totalIncomes += $transaction->getAmount();
                } else {
                    $totalExpenses += $transaction->getAmount();
                }
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'budgets' => $budgets,
            'totalBalance' => $totalBalance,
            'totalIncomes' => $totalIncomes,
            'totalExpenses' => $totalExpenses,
            'budgetCount' => count($budgets),
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        }

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            if ($password !== $passwordConfirm) {
                $this->addFlash('error', 'Hasła nie są identyczne');
                return $this->render('auth/register.html.twig');
            }

            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Użytkownik o tym adresie email już istnieje');
                return $this->render('auth/register.html.twig');
            }

            $user = new User();
            $user->setEmail($email);
            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $user->setIsActive(true);

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->render('auth/register.html.twig');
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Konto zostało utworzone. Możesz się teraz zalogować.');
            return $this->redirectToRoute('login');
        }

        return $this->render('auth/register.html.twig');
    }

    #[Route('/change-password', name: 'change_password')]
    #[IsGranted('ROLE_USER')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            $user = $this->getUser();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Aktualne hasło jest nieprawidłowe');
                return $this->render('auth/change_password.html.twig');
            }

            if (strlen($newPassword) < 6) {
                $this->addFlash('error', 'Nowe hasło musi mieć minimum 6 znaków');
                return $this->render('auth/change_password.html.twig');
            }

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Nowe hasło i potwierdzenie muszą być identyczne');
                return $this->render('auth/change_password.html.twig');
            }

            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $entityManager->flush();

            $this->addFlash('success', 'Hasło zostało zmienione pomyślnie');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('auth/change_password.html.twig');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
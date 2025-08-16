<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/budgets')]
#[IsGranted('ROLE_USER')]
class WebBudgetController extends AbstractController
{
    #[Route('', name: 'budgets_index')]
    public function index(): Response
    {
        $user = $this->getUser();
        $budgets = $user->getBudgets();

        return $this->render('budgets/index.html.twig', [
            'budgets' => $budgets,
        ]);
    }

    #[Route('/new', name: 'budgets_new')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $description = $request->request->get('description');

            if (empty($name)) {
                $this->addFlash('error', 'Nazwa budżetu jest wymagana');
                return $this->redirectToRoute('dashboard');
            }

            $budget = new Budget();
            $budget->setName($name);
            $budget->setDescription($description ?: null);
            $budget->setUser($this->getUser());

            $errors = $validator->validate($budget);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->redirectToRoute('dashboard');
            }

            $entityManager->persist($budget);
            $entityManager->flush();

            $this->addFlash('success', 'Budżet został utworzony');
            return $this->redirectToRoute('budgets_show', ['id' => $budget->getId()]);
        }

        // If not POST, redirect to dashboard (modal is used)
        return $this->redirectToRoute('dashboard');
    }

    #[Route('/{id}', name: 'budgets_show')]
    public function show(string $id, EntityManagerInterface $entityManager): Response
    {
        $budget = $entityManager->getRepository(Budget::class)->find($id);

        if (!$budget || $budget->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Budżet nie został znaleziony');
            return $this->redirectToRoute('budgets_index');
        }

        // Get transactions sorted by creation date (newest first)
        $transactions = $entityManager->getRepository(Transaction::class)->findBy(
            ['budget' => $budget],
            ['createdAt' => 'DESC']
        );

        return $this->render('budgets/show.html.twig', [
            'budget' => $budget,
            'transactions' => $transactions,
        ]);
    }

    #[Route('/{id}/transactions/new', name: 'transactions_new')]
    public function newTransaction(
        string $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        $budget = $entityManager->getRepository(Budget::class)->find($id);

        if (!$budget || $budget->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Budżet nie został znaleziony');
            return $this->redirectToRoute('budgets_index');
        }

        if ($request->isMethod('POST')) {
            $amount = $request->request->get('amount');
            $type = $request->request->get('type');
            $comment = $request->request->get('comment');
            $source = $request->request->get('source', 'budgets_show');

            // Normalize and validate amount (replace comma with dot)
            if ($amount) {
                $amount = str_replace(',', '.', $amount);
                
                // Validate decimal places (max 2)
                if (strpos($amount, '.') !== false) {
                    $parts = explode('.', $amount);
                    if (isset($parts[1]) && strlen($parts[1]) > 2) {
                        $this->addFlash('error', 'Kwota może mieć maksymalnie 2 miejsca po przecinku');
                        return $this->getRedirectForSource($source, $budget->getId());
                    }
                }
            }

            if (empty($amount) || empty($type) || empty($comment)) {
                $this->addFlash('error', 'Wszystkie pola są wymagane');
                return $this->getRedirectForSource($source, $budget->getId());
            }

            $transaction = new Transaction();
            $transaction->setAmount((string) $amount);
            $transaction->setType($type);
            $transaction->setComment($comment);
            $transaction->setBudget($budget);

            $errors = $validator->validate($transaction);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->getRedirectForSource($source, $budget->getId());
            }

            $entityManager->persist($transaction);
            $entityManager->flush();

            $this->addFlash('success', 'Transakcja została dodana do budżetu "' . $budget->getName() . '"');
            return $this->getRedirectForSource($source, $budget->getId());
        }

        // If not POST, redirect to budget show (modal is used)
        return $this->redirectToRoute('budgets_show', ['id' => $budget->getId()]);
    }

    private function getRedirectForSource(string $source, string $budgetId): Response
    {
        return match ($source) {
            'budgets_index' => $this->redirectToRoute('budgets_index'),
            'dashboard' => $this->redirectToRoute('dashboard'),
            default => $this->redirectToRoute('budgets_show', ['id' => $budgetId]),
        };
    }
}
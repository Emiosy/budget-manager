<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[Route('/api/budgets/{budgetId}/transactions')]
#[IsGranted('ROLE_USER')]
class TransactionController extends AbstractController
{
    #[Route('', name: 'api_transactions_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/budgets/{budgetId}/transactions',
        summary: 'Get budget transactions',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'budgetId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of budget transactions',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            'id' => new OA\Property(property: 'id', type: 'string', format: 'int64'),
                            'amount' => new OA\Property(property: 'amount', type: 'number', format: 'float'),
                            'type' => new OA\Property(property: 'type', type: 'string', enum: ['income', 'expense']),
                            'comment' => new OA\Property(property: 'comment', type: 'string'),
                            'created_at' => new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        ]
                    )
                )
            ),
            new OA\Response(response: 404, description: 'Budget not found')
        ]
    )]
    public function index(string $budgetId, EntityManagerInterface $entityManager): JsonResponse
    {
        $budget = $this->getBudgetForUser($budgetId, $entityManager);
        if (!$budget) {
            return new JsonResponse(['error' => 'Budget not found'], 404);
        }

        $data = [];
        foreach ($budget->getTransactions() as $transaction) {
            $data[] = [
                'id' => $transaction->getId(),
                'amount' => $transaction->getAmount(),
                'type' => $transaction->getType(),
                'comment' => $transaction->getComment(),
                'created_at' => $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('', name: 'api_transactions_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/budgets/{budgetId}/transactions',
        summary: 'Create new transaction',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'budgetId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    'amount' => new OA\Property(property: 'amount', type: 'number', format: 'float'),
                    'type' => new OA\Property(property: 'type', type: 'string', enum: ['income', 'expense']),
                    'comment' => new OA\Property(property: 'comment', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Transaction created successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        'id' => new OA\Property(property: 'id', type: 'string', format: 'int64'),
                        'amount' => new OA\Property(property: 'amount', type: 'number', format: 'float'),
                        'type' => new OA\Property(property: 'type', type: 'string'),
                        'comment' => new OA\Property(property: 'comment', type: 'string'),
                        'created_at' => new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 404, description: 'Budget not found')
        ]
    )]
    public function create(
        string $budgetId,
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $budget = $this->getBudgetForUser($budgetId, $entityManager);
        if (!$budget) {
            return new JsonResponse(['error' => 'Budget not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['amount']) || !isset($data['type']) || !isset($data['comment'])) {
            return new JsonResponse(['error' => 'Amount, type and comment are required'], 400);
        }

        $transaction = new Transaction();
        $transaction->setAmount((string) $data['amount']);
        $transaction->setType($data['type']);
        $transaction->setComment($data['comment']);
        $transaction->setBudget($budget);

        $errors = $validator->validate($transaction);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $entityManager->persist($transaction);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $transaction->getId(),
            'amount' => $transaction->getAmount(),
            'type' => $transaction->getType(),
            'comment' => $transaction->getComment(),
            'created_at' => $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
        ], 201);
    }

    private function getBudgetForUser(string $budgetId, EntityManagerInterface $entityManager): ?Budget
    {
        $budget = $entityManager->getRepository(Budget::class)->find($budgetId);
        
        if (!$budget || $budget->getUser() !== $this->getUser()) {
            return null;
        }

        return $budget;
    }
}
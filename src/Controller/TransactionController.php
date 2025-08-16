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
#[OA\Tag(name: 'Transactions', description: 'Transaction management - add income and expenses to your budgets')]
class TransactionController extends AbstractController
{
    #[Route('', name: 'api_transactions_index', methods: ['GET'])]
    #[OA\Get(
        path: '/budgets/{budgetId}/transactions',
        summary: 'Get all transactions for a budget',
        description: 'Retrieves a complete list of all income and expense transactions for a specific budget, ordered by creation date.',
        tags: ['Transactions'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'budgetId',
                in: 'path',
                required: true,
                description: 'Unique budget identifier (UUID)',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: 'a4b1c2d3-e4f5-6789-abcd-ef1234567890'
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of all transactions in the budget',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            'id' => new OA\Property(property: 'id', type: 'integer', format: 'int64', example: 15),
                            'amount' => new OA\Property(property: 'amount', type: 'number', format: 'float', example: 250.00),
                            'type' => new OA\Property(property: 'type', type: 'string', enum: ['income', 'expense'], example: 'income'),
                            'comment' => new OA\Property(property: 'comment', type: 'string', example: 'Salary payment'),
                            'created_at' => new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-01-15 14:30:00'),
                        ]
                    ),
                    example: [
                        [
                            'id' => 15,
                            'amount' => 1500.00,
                            'type' => 'income',
                            'comment' => 'Monthly salary',
                            'created_at' => '2025-01-15 09:00:00'
                        ],
                        [
                            'id' => 16,
                            'amount' => 450.50,
                            'type' => 'expense',
                            'comment' => 'Groceries and household items',
                            'created_at' => '2025-01-15 18:30:00'
                        ]
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Budget not found or access denied',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        'error' => new OA\Property(property: 'error', type: 'string', example: 'Budget not found')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Authentication required',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found')
                    ]
                )
            )
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
        path: '/budgets/{budgetId}/transactions',
        summary: 'Add new transaction to budget',
        description: 'Creates a new income or expense transaction within a specific budget. The transaction amount will immediately affect the budget balance calculation.',
        tags: ['Transactions'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'budgetId',
                in: 'path',
                required: true,
                description: 'Unique budget identifier (UUID)',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: 'a4b1c2d3-e4f5-6789-abcd-ef1234567890'
            )
        ],
        requestBody: new OA\RequestBody(
            description: 'Transaction data',
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['amount', 'type', 'comment'],
                properties: [
                    'amount' => new OA\Property(
                        property: 'amount',
                        type: 'number',
                        format: 'float',
                        description: 'Transaction amount (positive number for both income and expenses)',
                        minimum: 0.01,
                        example: 250.50
                    ),
                    'type' => new OA\Property(
                        property: 'type',
                        type: 'string',
                        enum: ['income', 'expense'],
                        description: 'Transaction type - income adds to budget, expense subtracts from budget',
                        example: 'income'
                    ),
                    'comment' => new OA\Property(
                        property: 'comment',
                        type: 'string',
                        description: 'Description or note about the transaction',
                        maxLength: 255,
                        example: 'Freelance project payment'
                    ),
                ],
                example: [
                    'amount' => 750.00,
                    'type' => 'income',
                    'comment' => 'Freelance web development project'
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
                        'id' => new OA\Property(property: 'id', type: 'integer', format: 'int64', example: 42),
                        'amount' => new OA\Property(property: 'amount', type: 'number', format: 'float', example: 750.00),
                        'type' => new OA\Property(property: 'type', type: 'string', example: 'income'),
                        'comment' => new OA\Property(property: 'comment', type: 'string', example: 'Freelance web development project'),
                        'created_at' => new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-01-16 10:15:30'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error - invalid data provided',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        'error' => new OA\Property(property: 'error', type: 'string', example: 'Amount, type and comment are required'),
                        'errors' => new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['Amount must be greater than 0', 'Transaction type must be either income or expense']
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Budget not found or access denied',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        'error' => new OA\Property(property: 'error', type: 'string', example: 'Budget not found')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Authentication required',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found')
                    ]
                )
            )
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
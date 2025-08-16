<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\DTO\BudgetCreateDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[Route('/api/budgets')]
#[IsGranted('ROLE_USER')]
class BudgetController extends AbstractController
{
    #[Route('', name: 'api_budgets_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/budgets',
        summary: 'Get all user budgets',
        description: 'Returns a list of all budgets belonging to the authenticated user, including calculated balances.',
        tags: ['Budgets'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of user budgets with calculated balances',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            'id' => new OA\Property(property: 'id', type: 'string', format: 'uuid', example: 'a4b1c2d3-e4f5-6789-abcd-ef1234567890'),
                            'name' => new OA\Property(property: 'name', type: 'string', example: 'Holiday Savings'),
                            'description' => new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Money for summer vacation'),
                            'balance' => new OA\Property(property: 'balance', type: 'number', format: 'float', example: 1250.50),
                            'created_at' => new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-01-15 10:30:00'),
                        ]
                    )
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
    public function index(): JsonResponse
    {
        $user = $this->getUser();
        $budgets = $user->getBudgets();

        $data = [];
        foreach ($budgets as $budget) {
            $data[] = [
                'id' => $budget->getId(),
                'name' => $budget->getName(),
                'description' => $budget->getDescription(),
                'balance' => $budget->getBalance(),
                'created_at' => $budget->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('', name: 'api_budgets_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/budgets',
        summary: 'Create new budget',
        description: 'Creates a new budget for the authenticated user with a required name and optional description.',
        tags: ['Budgets'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Budget creation data',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: BudgetCreateDTO::class))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Budget created successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        'id' => new OA\Property(property: 'id', type: 'string', format: 'uuid', example: 'a4b1c2d3-e4f5-6789-abcd-ef1234567890'),
                        'name' => new OA\Property(property: 'name', type: 'string', example: 'Holiday Savings'),
                        'description' => new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Money for summer vacation'),
                        'balance' => new OA\Property(property: 'balance', type: 'number', format: 'float', example: 0.00),
                        'created_at' => new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-01-15 10:30:00'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error - invalid data provided',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        'error' => new OA\Property(property: 'error', type: 'string', example: 'Budget name is required'),
                        'errors' => new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['Budget name cannot exceed 255 characters']
                        )
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
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty($data['name'])) {
            return new JsonResponse(['error' => 'Budget name is required'], 400);
        }

        $budget = new Budget();
        $budget->setName($data['name']);
        $budget->setDescription($data['description'] ?? null);
        $budget->setUser($this->getUser());

        $errors = $validator->validate($budget);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $entityManager->persist($budget);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $budget->getId(),
            'name' => $budget->getName(),
            'description' => $budget->getDescription(),
            'balance' => $budget->getBalance(),
            'created_at' => $budget->getCreatedAt()->format('Y-m-d H:i:s'),
        ], 201);
    }

    #[Route('/{id}', name: 'api_budgets_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/budgets/{id}',
        summary: 'Get specific budget',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Budget details',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        'id' => new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        'name' => new OA\Property(property: 'name', type: 'string'),
                        'description' => new OA\Property(property: 'description', type: 'string', nullable: true),
                        'balance' => new OA\Property(property: 'balance', type: 'number', format: 'float'),
                        'created_at' => new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Budget not found')
        ]
    )]
    public function show(string $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $budget = $entityManager->getRepository(Budget::class)->find($id);

        if (!$budget || $budget->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Budget not found'], 404);
        }

        return new JsonResponse([
            'id' => $budget->getId(),
            'name' => $budget->getName(),
            'description' => $budget->getDescription(),
            'balance' => $budget->getBalance(),
            'created_at' => $budget->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
    }
}
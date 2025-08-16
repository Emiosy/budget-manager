<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\DTO\UserLoginDTO;
use App\DTO\UserRegistrationDTO;
use App\DTO\ChangePasswordDTO;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Tag;

#[Route('/api/auth')]
#[OA\Tag(name: 'Authentication', description: 'User registration, login and password management')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    /**
     * Register a new user account
     * 
     * Creates a new user account with email and password. The user will be active by default.
     */
    #[Post(
        path: '/auth/register',
        summary: 'Register a new user account',
        description: 'Creates a new user account with email and password. The user will be active by default.',
        tags: ['Authentication']
    )]
    #[RequestBody(
        description: 'User registration data',
        required: true,
        content: new JsonContent(
            required: ['email', 'password'],
            properties: [
                'email' => new Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                'password' => new Property(property: 'password', type: 'string', minLength: 6, example: 'password123')
            ]
        )
    )]
    #[Response(
        response: 201,
        description: 'User registered successfully',
        content: new JsonContent(
            properties: [
                'message' => new Property(property: 'message', type: 'string', example: 'User registered successfully'),
                'user_id' => new Property(property: 'user_id', type: 'string', format: 'uuid', example: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890')
            ]
        )
    )]
    #[Response(
        response: 400,
        description: 'Validation error or user already exists',
        content: new JsonContent(
            properties: [
                'error' => new Property(property: 'error', type: 'string', example: 'User with this email already exists'),
                'errors' => new Property(
                    property: 'errors',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['Password must be at least 6 characters long']
                )
            ]
        )
    )]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required'], 400);
        }

        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'User with this email already exists'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setIsActive(true);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'User registered successfully',
            'user_id' => $user->getId()
        ], 201);
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    /**
     * Authenticate user and get JWT token
     * 
     * Authenticates user with email and password, returns a JWT token for API access.
     */
    #[OA\Post(
        path: '/auth/login',
        summary: 'Authenticate user and get JWT token',
        description: 'Authenticates user with email and password, returns a JWT token for API access.',
        tags: ['Authentication']
    )]
    #[OA\RequestBody(
        description: 'User login credentials',
        required: true,
        content: new OA\JsonContent(
            required: ['username', 'password'],
            properties: [
                'username' => new OA\Property(property: 'username', type: 'string', format: 'email', example: 'test@example.com'),
                'password' => new OA\Property(property: 'password', type: 'string', example: 'password123')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Authentication successful, JWT token returned',
        content: new OA\JsonContent(
            properties: [
                'token' => new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJ1c2VybmFtZSI6InRlc3RAZXhhbXBsZS5jb20iLCJyb2xlcyI6WyJST0xFX1VTRVIiXSwiaWF0IjoxNjQyNjg0ODAwLCJleHAiOjE2NDI2ODg0MDB9.example')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid credentials or inactive account',
        content: new OA\JsonContent(
            properties: [
                'message' => new OA\Property(property: 'message', type: 'string', example: 'Invalid credentials.')
            ]
        )
    )]
    public function login(): JsonResponse
    {
        return new JsonResponse(['message' => 'This endpoint is handled by lexik_jwt_authentication'], 200);
    }

    #[Route('/change-password', name: 'api_change_password', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        path: '/auth/change-password',
        summary: 'Change user password',
        description: 'Changes the authenticated user\'s password. Requires current password verification.',
        tags: ['Authentication'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Password change data',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: ChangePasswordDTO::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password changed successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Password changed successfully'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error or incorrect current password',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        'error' => new OA\Property(property: 'error', type: 'string', example: 'Current password is incorrect'),
                        'errors' => new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['New password must be at least 6 characters long']
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
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $dto = new ChangePasswordDTO();
        $dto->currentPassword = $data['currentPassword'] ?? '';
        $dto->newPassword = $data['newPassword'] ?? '';
        $dto->confirmPassword = $data['confirmPassword'] ?? '';

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $user = $this->getUser();
        if (!$passwordHasher->isPasswordValid($user, $dto->currentPassword)) {
            return new JsonResponse(['error' => 'Current password is incorrect'], 400);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $dto->newPassword));
        $entityManager->flush();

        return new JsonResponse(['message' => 'Password changed successfully'], 200);
    }
}
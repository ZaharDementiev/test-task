<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final readonly class AuthService
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function register(array $data): string
    {
        /** @var User $user */
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->createToken('auth_token')->plainTextToken;

        return $user->createToken('auth_token')->plainTextToken;
    }

    public function login(array $data): string
    {
        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            throw new Exception('Invalid login details');
        }

        $user = $this->userRepository->findOrFailByFields($data['email']);

        return $user->createToken('auth_token')->plainTextToken;
    }

    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}

<?php
declare(strict_types=1);

namespace App\DTOs\Auth;

final class LoginDTO
{
    public function __construct(
        public readonly string $phone,
        public readonly string $password,
        public readonly bool $remember = false,
    ) {}

    public static function fromRequest(\App\Http\Requests\Auth\LoginRequest $request): self
    {
        return new self(
            phone: trim((string)$request->validated('phone')),
            password: (string)$request->validated('password'),
            remember: (bool)$request->boolean('remember', false),
        );
    }
}

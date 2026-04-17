<?php

namespace App\Support;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class JwtAuth
{
    public static function issue(array $claims): string
    {
        $now = time();
        $ttl = (int) env('JWT_TTL_SECONDS', 86400);

        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + $ttl,
        ]);

        return JWT::encode($payload, self::secret(), 'HS256');
    }

    public static function decode(?string $token): ?array
    {
        if (!$token) {
            return null;
        }

        try {
            $decoded = JWT::decode($token, new Key(self::secret(), 'HS256'));
            return (array) $decoded;
        } catch (Throwable $e) {
            return null;
        }
    }

    private static function secret(): string
    {
        $secret = env('JWT_SECRET');
        if (!empty($secret)) {
            return $secret;
        }

        $appKey = (string) config('app.key', '');
        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $appKey !== '' ? $appKey : 'gabay-health-default-jwt-secret';
    }
}


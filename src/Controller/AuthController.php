<?php

namespace App\Controller;

use App\Model\User;
use PDO;

class AuthController
{
    private User $userModel;

    public function __construct(PDO $db)
    {
        $this->userModel = new User($db);
    }

    public function registerWithToken(string $token, string $steamId): bool
    {
        $existing = $this->userModel->findByToken($token);
        if (!$existing) return false;

        if (!empty($existing['SteamId'])) return false;

        // ✅ проверяем, что такой SteamID есть в ArkShopPlayers
        if (!$this->userModel->checkSteamIdExists($steamId)) return false;

        return $this->userModel->registerSteamId($token, $steamId);
    }

    public function loginBySteamId(string $steamId): ?array
    {
        return $this->userModel->findBySteamId($steamId);
    }
}

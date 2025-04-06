<?php

namespace App\Model;

use PDO;
use App\Service\SessionActivity;
use App\Config\Config;

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE Token = :token");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function registerSteamId(string $token, string $steamId): bool
    {
        $stmt = $this->db->prepare("UPDATE user SET SteamId = :steamId WHERE Token = :token AND SteamId IS NULL");
        return $stmt->execute(['steamId' => $steamId, 'token' => $token]);
    }

    public function findBySteamId(string $steamId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE SteamId = :steamId");
        $stmt->execute(['steamId' => $steamId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function checkSteamIdExists(string $steamId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM ArkShopPlayers WHERE SteamId = :steamId");
        $stmt->execute(['steamId' => $steamId]);
        return (bool) $stmt->fetchColumn();
    }

    public function isTokenValid(string $token): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM ArkShopPlayers WHERE Token = :token");
        $stmt->execute(['token' => $token]);
        return (bool) $stmt->fetchColumn();
    }

    public function getSteamIdByToken(string $token): ?string
    {
        $stmt = $this->db->prepare("SELECT SteamId FROM ArkShopPlayers WHERE Token = :token");
        $stmt->execute(['token' => $token]);
        return $stmt->fetchColumn() ?: null;
    }

    public function isEmailTaken(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM user WHERE Email = :email");
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    public function isNameTaken(string $name): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM user WHERE Name = :name");
        $stmt->execute(['name' => $name]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(string $email, string $name, string $password, string $token, string $steamId): bool
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO user (Email, Name, Password, Token, SteamId) 
            VALUES (:email, :name, :password, :token, :steamId)");
        return $stmt->execute([
            'email' => $email,
            'name' => $name,
            'password' => $hash,
            'token' => $token,
            'steamId' => $steamId
        ]);
    }


    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE Email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updatePassword(int $userId, string $newPassword): bool
    {

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE user SET Password = :hash WHERE Id = :id");
        SessionActivity::record('U');
        SessionActivity::setPasswordChangeTime();
        return $stmt->execute(['hash' => $hash, 'id' => $userId]);
    }

    public function getPlayerWallet(string $steamId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT Points, TotalSpent, LastTime 
            FROM ArkShopPlayers 
            WHERE SteamId = :steamId
        ");
        $stmt->execute(['steamId' => $steamId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function isAdmin(array $user): bool
    {
        return ($user['SteamID'] ?? '') === Config::ADMIN_STEAM_ID;
    }
}

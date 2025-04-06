<?php

namespace App\Service;

use PDO;
use App\Config\Config as Config;

class Wallet
{
    public const ADMIN_STEAM_ID = '101';

    public static function refresh(PDO $db, string $steamId): void
    {
        $stmt = $db->prepare("SELECT Points FROM ArkShopPlayers WHERE SteamId = :steamId");
        $stmt->execute(['steamId' => $steamId]);
        $points = $stmt->fetchColumn();

        if (!is_numeric($points)) {
            $points = 0;
        }

        $_SESSION['wallet'] = (int) $points;
    }

    public static function getBalance(): int
    {
        return $_SESSION['wallet'] ?? 0;
    }

    public static function spend(PDO $db, string $steamId, int $amount): void
    {
        if ($steamId === Config::ADMIN_STEAM_ID) {
            return;
        }

        $stmt = $db->prepare("UPDATE ArkShopPlayers 
            SET Points = Points - :amount, 
                TotalSpent = TotalSpent + :amount 
            WHERE SteamId = :steamId");
        $stmt->execute(['amount' => $amount, 'steamId' => $steamId]);

        self::refresh($db, $steamId);
    }
}

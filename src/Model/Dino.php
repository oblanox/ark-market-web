<?php

namespace App\Model;

use PDO;

class Dino
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getEnabled(): array
    {
        $stmt = $this->db->query("SELECT * FROM arkshop_dino WHERE Enable = 1 ORDER BY NameRU");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVisibleForUser(array $user): array
    {
        $isAdmin = ($user['SteamId'] ?? '') == \App\Config\Config::ADMIN_STEAM_ID;

        if ($isAdmin) {
            $stmt = $this->db->query("SELECT * FROM arkshop_dino ORDER BY NameRU");
        } else {
            $stmt = $this->db->query("SELECT * FROM arkshop_dino WHERE Enable = 1 ORDER BY NameRU");
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

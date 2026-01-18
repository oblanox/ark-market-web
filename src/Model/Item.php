<?php

namespace App\Model;

use PDO;

class Item
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Получить все товары, доступные для отображения в магазине
     */
    public function getVisibleForUser(array $user = []): array
    {
        $isAdmin = ($user['SteamId'] ?? null) == \App\Config\Config::ADMIN_STEAM_ID;

        $sql = "SELECT * FROM items WHERE 1";

        if (!$isAdmin) {
            $sql .= " AND Enable = 1 AND Visible = 1";
        }

        $sql .= " ORDER BY Type, NameRU";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Получить один товар по ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM items WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        return $item ?: null;
    }

    /**
     * Добавить товар (используется в админке)
     */
    public function create(array $data): bool
    {
        $verifed = $this->verify($data);
        $stmt = $this->pdo->prepare("
            INSERT INTO items (NameRU, NameEN, Pic, Price, ShortCode, Type, StackSize, Enable, Visible, HasQuality, DefaultQuality, Customizable, Note, Code)
            VALUES (:NameRU, :NameEN, :Pic, :Price, :ShortCode, :Type, :StackSize, :Enable, :Visible, :HasQuality, :DefaultQuality, :Customizable, :Note, :Code)
        ");

        return $stmt->execute([
            'NameRU'        => $data['NameRU'] ?? '',
            'NameEN'        => $data['NameEN'] ?? '',
            'Pic'           => $data['Pic'] ?? '',
            'Price'         => $data['Price'] ?? 0,
            'ShortCode'     => $data['ShortCode'] ?? '',
            'Type'          => $data['Type'] ?? '',
            'StackSize'     => $data['StackSize'] ?? 1,
            'Enable'        => $verifed['Enable'],
            'Visible'       => $verifed['Visible'],
            'HasQuality'    => $verifed['HasQuality'],
            'DefaultQuality' => $verifed['DefaultQuality'] ?? null,
            'Customizable'  => $verifed['Customizable'],
            'Note'          => $data['Note'] ?? null,
            'Code'          => $data['Code'] ?? null,
        ]);
    }

    /**
     * Обновить товар
     */
    public function update(int $id, array $data): bool
    {
        $verifed = $this->verify($data);
        $stmt = $this->pdo->prepare("
            UPDATE items SET
                NameRU = :NameRU,
                NameEN = :NameEN,
                Pic = :Pic,
                Price = :Price,
                ShortCode = :ShortCode,
                Type = :Type,
                StackSize = :StackSize,
                Enable = :Enable,
                Visible = :Visible,
                HasQuality = :HasQuality,
                DefaultQuality = :DefaultQuality,
                Customizable = :Customizable,
                Note = :Note,
                Code = :Code
            WHERE id = :id
        ");

        return $stmt->execute([
            'id'            => $id,
            'NameRU'        => $data['NameRU'] ?? '',
            'NameEN'        => $data['NameEN'] ?? '',
            'Pic'           => $data['Pic'] ?? '',
            'Price'         => $data['Price'] ?? 0,
            'ShortCode'     => $data['ShortCode'] ?? '',
            'Type'          => $data['Type'] ?? '',
            'StackSize'     => $data['StackSize'] ?? 1,
            'Enable'        => $verifed['Enable'],
            'Visible'       => $verifed['Visible'],
            'HasQuality'    => $verifed['HasQuality'],
            'DefaultQuality' => $verifed['DefaultQuality'] ?? null,
            'Customizable'  => $verifed['Customizable'],
            'Note'          => $data['Note'] ?? null,
            'Code'          => $data['Code'] ?? null,
        ]);
    }

    /**
     * Удалить товар
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM items WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    private function verify(array $data): array
    {
        $allowedQualities = ['Primitive', 'Ramshackle', 'Apprentice', 'Journeyman', 'Mastercraft', 'Ascendant'];

        return [
            'Enable'       => is_null($data['Enable']) ? 0 : $data['Enable'],
            'Visible'      => is_null($data['Visible']) ? 0 : $data['Visible'],
            'HasQuality'   => is_null($data['HasQuality']) ? 0 : $data['HasQuality'],
            'Customizable' => is_null($data['Customizable']) ? 0 : $data['Customizable'],
            'DefaultQuality' => in_array($data['DefaultQuality'] ?? null, $allowedQualities, true)
                ? $data['DefaultQuality']
                : null,
        ];
    }
}

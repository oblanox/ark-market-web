<?php

namespace App\Service;

class SessionActivity
{
    private const DEFAULT_STATE = [
        'C' => 0,
        'R' => 0,
        'U' => 0,
        'D' => 0,
        'LastTime' => null,
        'LastTimeChangePassword' => null
    ];

    public static function init(): void
    {
        if (!isset($_SESSION['activity'])) {
            $_SESSION['activity'] = self::DEFAULT_STATE;
        }
    }

    public static function record(string $type): void
    {
        self::init();

        if (in_array($type, ['C', 'R', 'U', 'D'])) {
            $_SESSION['activity'][$type]++;
        }

        $_SESSION['activity']['LastTime'] = time();
    }

    public static function get(): array
    {
        self::init();
        return $_SESSION['activity'];
    }

    public static function total(): int
    {
        self::init();
        return $_SESSION['activity']['C'] +
            $_SESSION['activity']['R'] +
            $_SESSION['activity']['U'] +
            $_SESSION['activity']['D'];
    }

    public static function reset(): void
    {
        $_SESSION['activity'] = self::DEFAULT_STATE;
    }

    public static function lastActionTime(): ?int
    {
        self::init();
        return $_SESSION['activity']['LastTime'];
    }

    public static function setPasswordChangeTime(): void
    {
        self::init();
        $_SESSION['activity']['LastTimeChangePassword'] = time();
    }

    public static function getPasswordChangeTime(): ?int
    {
        self::init();
        return $_SESSION['activity']['LastTimeChangePassword'];
    }
}

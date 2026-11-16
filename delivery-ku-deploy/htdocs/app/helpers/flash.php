<?php
declare(strict_types=1);

function flash_set(string $type, string $message): void
{
    $_SESSION['_flashes'][] = ['type' => $type, 'message' => $message];
}

function flash_get_all(): array
{
    $flashes = $_SESSION['_flashes'] ?? [];
    unset($_SESSION['_flashes']);
    return $flashes;
}

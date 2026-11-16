<?php
declare(strict_types=1);

final class Validator
{
    public static function required(array $data, array $fields): array
    {
        $errors = [];
        foreach ($fields as $key => $label) {
            $val = trim((string)($data[$key] ?? ''));
            if ($val === '') {
                $errors[] = "{$label} wajib diisi.";
            }
        }
        return $errors;
    }

    public static function email(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function phone(string $phone): bool
    {
        return preg_match('/^[0-9+\-\s]{8,20}$/', trim($phone)) === 1;
    }

    public static function positiveNumber(mixed $val): bool
    {
        return is_numeric($val) && (float)$val > 0;
    }
}

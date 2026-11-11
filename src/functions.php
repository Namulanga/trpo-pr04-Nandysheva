<?php
declare(strict_types=1);

const APP_VERSION = "1.0";
const DATA_DIR = __DIR__ . '/../data';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
function normalizeEmployee(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => trim($row['name']),
        'department' => trim($row['department']),
        'experience' => (int) $row['experience'],
        'email' => trim($row['email']),
    ];
}

function loadEmployees(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $handle = fopen($path, 'r');

    if ($handle === false) {
        return [];
    }

    $header = fgetcsv($handle, 0, ';', '"', '');

    if ($header === false) {
        fclose($handle);
        return [];
    }

    $header[0] = ltrim($header[0], "\xEF\xBB\xBF");

    $employees = [];

    while (($row = fgetcsv($handle, 0, ';', '"', '')) !== false) {
        if (count($row) !== count($header)) {
            continue;
        }

        $employees[] = normalizeEmployee(array_combine($header, $row));
    }

    fclose($handle);

    return $employees;
}

function departmentsOf(array $employees): array
{
    return array_values(
        array_unique(array_column($employees, 'department'))
    );
}

function inputValue(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function filterEmployees(array $employees, string $department): array
{
    if ($department === '') {
        return $employees;
    }
    return array_values(array_filter(
        $employees,
        fn (array $employee): bool => $employee['department'] === $department
    ));
}
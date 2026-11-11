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
function validateInput(array $input, array $departments): array
{
    $errors = [];

    $name = trim((string) ($input['name'] ?? ''));
    if ($name === '') {
        $errors['name'] = 'Укажите имя';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 60) {
        $errors['name'] = 'Имя должно быть от 2 до 60 символов';
    }

    $email = trim((string) ($input['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный email';
    }

    $department = trim((string) ($input['department'] ?? ''));
    if (!in_array($department, $departments, true)) {
        $errors['department'] = 'Выберите отдел из списка';
    }

    $experience = filter_var(
        trim((string) ($input['experience'] ?? '')),
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0, 'max_range' => 50]]
    );
    if ($experience === false) {
        $errors['experience'] = 'Стаж — целое число от 0 до 50';
    }

    return $errors;
}
function loadCases(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function runCases(array $cases, array $departments): array
{
    $defaults = [
        'email'      => 'test@example.test',
        'department' => $departments[0] ?? '',
    ];

    $results = [];
    foreach ($cases as $case) {
        $errors = validateInput($case + $defaults, $departments);
        $isValid = $errors === [];
        $results[] = [
            'case'     => (string) ($case['case'] ?? ''),
            'expected' => (bool) ($case['valid'] ?? false),
            'actual'   => $isValid,
            'errors'   => $errors,
            'ok'       => $isValid === (bool) ($case['valid'] ?? false),
        ];
    }
    return $results;
}
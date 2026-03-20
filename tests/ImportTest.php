<?php

declare(strict_types=1);

use Tester\Assert;
use NImport\ImportProcessor;
use NImport\ImportResult;

require __DIR__ . '/../../../vendor/autoload.php';
Tester\Environment::setup();

// Test basic import
$proc = new ImportProcessor();
$imported = [];
$proc->onImportRow[] = function (array $row) use (&$imported): void {
    $imported[] = $row;
};

$rows = [
    ['name' => 'Widget A', 'price' => '100'],
    ['name' => 'Widget B', 'price' => '200'],
];
$result = $proc->process($rows);
Assert::same(2, $result->getTotal());
Assert::same(2, $result->getImported());
Assert::same(0, $result->getErrorCount());
Assert::true($result->isSuccess());
Assert::count(2, $imported);

// Test with validation
$proc2 = new ImportProcessor();
$proc2->onValidateRow[] = function (array $row): ?string {
    return ($row['price'] ?? '') === '' ? 'Price is required' : null;
};
$proc2->onImportRow[] = function (array $row): void {};

$rows2 = [
    ['name' => 'A', 'price' => '100'],
    ['name' => 'B', 'price' => ''],
    ['name' => 'C', 'price' => '300'],
];
$result2 = $proc2->process($rows2);
Assert::same(3, $result2->getTotal());
Assert::same(2, $result2->getImported());
Assert::same(1, $result2->getErrorCount());
Assert::false($result2->isSuccess());
Assert::contains('Price is required', $result2->getErrors()[1] ?? '');

// Test with transformation
$proc3 = new ImportProcessor();
$proc3->onBeforeRow[] = function (array $row): array {
    $row['name'] = strtoupper($row['name'] ?? '');
    return $row;
};
$imported3 = [];
$proc3->onImportRow[] = function (array $row) use (&$imported3): void {
    $imported3[] = $row;
};
$proc3->process([['name' => 'test']]);
Assert::same('TEST', $imported3[0]['name']);

// Test result summary
$r = new ImportResult();
$r->incrementTotal(); $r->incrementTotal(); $r->incrementTotal();
$r->incrementImported(); $r->incrementImported();
$r->addError(2, 'fail');
Assert::contains('2/3', $r->getSummary());
Assert::contains('1 errors', $r->getSummary());

echo "NImport: ALL TESTS PASSED\n";

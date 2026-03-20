<?php

declare(strict_types=1);

namespace NImport;

/**
 * Universal data import processor for Nette.
 *
 * Processes rows from any source (CSV, XML, API) through a pipeline:
 *   Source → Transform → Validate → Import → Report
 */
final class ImportProcessor
{
	/** @var list<callable(array<string, mixed>): array<string, mixed>> Transform row before import */
	public array $onBeforeRow = [];

	/** @var list<callable(array<string, mixed>): ?string> Validate row, return error or null */
	public array $onValidateRow = [];

	/** @var list<callable(array<string, mixed>): void> Import/persist the row */
	public array $onImportRow = [];

	/** @var list<callable(ImportResult): void> Called when import finishes */
	public array $onComplete = [];


	/**
	 * Process an iterable of rows.
	 *
	 * @param iterable<int, array<string, mixed>> $rows
	 */
	public function process(iterable $rows): ImportResult
	{
		$result = new ImportResult();
		$rowIndex = 0;

		foreach ($rows as $row) {
			$result->incrementTotal();

			try {
				// Transform
				foreach ($this->onBeforeRow as $handler) {
					$row = $handler($row);
				}

				// Validate
				$error = null;
				foreach ($this->onValidateRow as $validator) {
					$error = $validator($row);
					if ($error !== null) {
						break;
					}
				}

				if ($error !== null) {
					$result->addError($rowIndex, $error);
					$rowIndex++;
					continue;
				}

				// Import
				foreach ($this->onImportRow as $importer) {
					$importer($row);
				}

				$result->incrementImported();

			} catch (\Throwable $e) {
				$result->addError($rowIndex, $e->getMessage());
			}

			$rowIndex++;
		}

		// Complete
		foreach ($this->onComplete as $handler) {
			$handler($result);
		}

		return $result;
	}
}

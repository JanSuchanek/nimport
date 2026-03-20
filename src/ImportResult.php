<?php

declare(strict_types=1);

namespace NImport;

/**
 * Result of an import operation.
 */
final class ImportResult
{
	private int $total = 0;
	private int $imported = 0;
	/** @var array<int, string> */
	private array $errors = [];
	private float $startTime;


	public function __construct()
	{
		$this->startTime = microtime(true);
	}


	public function incrementTotal(): void { $this->total++; }
	public function incrementImported(): void { $this->imported++; }

	public function addError(int $rowIndex, string $message): void
	{
		$this->errors[$rowIndex] = $message;
	}

	public function getTotal(): int { return $this->total; }
	public function getImported(): int { return $this->imported; }
	public function getSkipped(): int { return $this->total - $this->imported - count($this->errors); }
	public function getErrorCount(): int { return count($this->errors); }

	/** @return array<int, string> */
	public function getErrors(): array { return $this->errors; }

	public function isSuccess(): bool { return $this->errors === []; }

	public function getDuration(): float
	{
		return round(microtime(true) - $this->startTime, 3);
	}

	/**
	 * Get a human-readable summary.
	 */
	public function getSummary(): string
	{
		return sprintf(
			'Imported %d/%d rows in %.1fs (%d errors)',
			$this->imported,
			$this->total,
			$this->getDuration(),
			count($this->errors),
		);
	}
}

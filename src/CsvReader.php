<?php

declare(strict_types=1);

namespace NImport;

/**
 * CSV file reader — parses CSV files into iterable rows.
 */
final class CsvReader
{
	private string $delimiter;
	private string $enclosure;
	private bool $hasHeader;


	public function __construct(
		private readonly string $filePath,
		string $delimiter = ';',
		string $enclosure = '"',
		bool $hasHeader = true,
	) {
		$this->delimiter = $delimiter;
		$this->enclosure = $enclosure;
		$this->hasHeader = $hasHeader;
	}


	/**
	 * Read CSV and yield associative arrays.
	 *
	 * @return \Generator<int, array<string, string>>
	 */
	public function getRows(): \Generator
	{
		$handle = fopen($this->filePath, 'r');
		if ($handle === false) {
			throw new \RuntimeException("Cannot open file: {$this->filePath}");
		}

		/** @var list<string>|null $headers */
		$headers = null;
		$index = 0;

		try {
			while (($row = fgetcsv($handle, 0, $this->delimiter, $this->enclosure)) !== false) {
				if ($row === [null]) {
					continue;
				}

				/** @var list<string> $row */

				if ($this->hasHeader && $headers === null) {
					$headers = array_map(fn(string $v): string => trim($v), $row);
					continue;
				}

				if ($headers !== null) {
					$row = array_pad($row, count($headers), '');
					yield $index => array_combine($headers, array_slice($row, 0, count($headers)));
				} else {
					yield $index => array_combine(array_map('strval', array_keys($row)), $row);
				}

				$index++;
			}
		} finally {
			fclose($handle);
		}
	}
}

<?php

declare(strict_types=1);

namespace NImport;

/**
 * XML feed reader — parses XML files into iterable rows.
 *
 * Usage:
 *   $reader = new XmlReader('/path/to/feed.xml', 'SHOPITEM');
 *   foreach ($reader->getRows() as $item) { ... }
 */
final class XmlReader
{
	public function __construct(
		private readonly string $filePath,
		private readonly string $itemTag,
	) {
	}


	/**
	 * Read XML and yield arrays for each matching element.
	 *
	 * @return \Generator<int, array<string, string>>
	 */
	public function getRows(): \Generator
	{
		$reader = new \XMLReader();
		if (!$reader->open($this->filePath)) {
			throw new \RuntimeException("Cannot open XML: {$this->filePath}");
		}

		$index = 0;
		while ($reader->read()) {
			if ($reader->nodeType === \XMLReader::ELEMENT && $reader->localName === $this->itemTag) {
				$xml = $reader->readOuterXml();
				if ($xml !== '') {
					$element = new \SimpleXMLElement($xml);
					yield $index => $this->elementToArray($element);
					$index++;
				}
			}
		}

		$reader->close();
	}


	/**
	 * Convert SimpleXMLElement to flat associative array.
	 * @return array<string, string>
	 */
	private function elementToArray(\SimpleXMLElement $element): array
	{
		$result = [];
		foreach ($element->children() as $child) {
			$name = $child->getName();
			if ($child->count() > 0) {
				// Nested elements — serialize as JSON
				$result[$name] = json_encode($this->elementToArray($child), JSON_UNESCAPED_UNICODE) ?: '';
			} else {
				$result[$name] = trim((string) $child);
			}
		}
		return $result;
	}
}

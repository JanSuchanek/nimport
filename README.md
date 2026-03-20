# NImport

Data import pipeline for Nette Framework — CSV/XML file processing with validation and progress tracking.

## Features

- 📥 **File Import** — CSV, XML, JSON source support
- ✅ **Validation** — Row-level validation with error reporting
- 📊 **Progress** — Batch processing with progress callbacks
- 🔄 **Pipeline** — Configurable import steps (parse → validate → transform → persist)
- ⚙️ **DI Extension** — Auto-registers import services

## Installation

```bash
composer require jansuchanek/nimport
```

## Configuration

```neon
extensions:
    import: NImport\DI\NImportExtension
```

## Usage

```php
use NImport\ImportPipeline;

$pipeline = $this->importPipeline;
$result = $pipeline->run('products.csv', [
    'delimiter' => ';',
    'encoding' => 'UTF-8',
]);

echo "Imported: {$result->getSuccessCount()}";
echo "Errors: {$result->getErrorCount()}";
```

## Requirements

- PHP >= 8.2
- Nette DI ^3.2

## License

MIT

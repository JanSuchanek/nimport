<?php

declare(strict_types=1);

namespace NImport\DI;

use NImport\ImportProcessor;
use Nette\DI\CompilerExtension;

/**
 * Nette DI Extension for NImport.
 */
final class NImportExtension extends CompilerExtension
{
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('processor'))
			->setFactory(ImportProcessor::class);
	}
}

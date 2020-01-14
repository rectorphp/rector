<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanAttributeTypeSyncer\Generator;

use Nette\Utils\FileSystem;
use PhpParser\Node\Stmt\Namespace_;
use Rector\PhpParser\Printer\BetterStandardPrinter;

abstract class AbstractAttributeAwareNodeGenerator
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @required
     */
    public function autowireAbstractAttributeAwareNodeGenerator(BetterStandardPrinter $betterStandardPrinter): void
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    protected function printNamespaceToFile(Namespace_ $namespace, string $targetFilePath): void
    {
        $fileContent = $this->betterStandardPrinter->prettyPrintFile([$namespace]);

        FileSystem::write($targetFilePath, $fileContent);
    }
}

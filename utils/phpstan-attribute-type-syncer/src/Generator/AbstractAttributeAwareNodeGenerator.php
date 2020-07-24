<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanAttributeTypeSyncer\Generator;

use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Symplify\SmartFileSystem\SmartFileSystem;

abstract class AbstractAttributeAwareNodeGenerator
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @required
     */
    public function autowireAbstractAttributeAwareNodeGenerator(
        BetterStandardPrinter $betterStandardPrinter,
        SmartFileSystem $smartFileSystem
    ): void {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->smartFileSystem = $smartFileSystem;
    }

    protected function printNamespaceToFile(Namespace_ $namespace, string $targetFilePath): void
    {
        $fileContent = $this->betterStandardPrinter->prettyPrintFile([$namespace]);

        $this->smartFileSystem->dumpFile($targetFilePath, $fileContent);
    }
}

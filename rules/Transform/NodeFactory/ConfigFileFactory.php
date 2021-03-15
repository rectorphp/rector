<?php

declare(strict_types=1);

namespace Rector\Transform\NodeFactory;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Testing\PhpConfigPrinter\PhpConfigPrinterFactory;
use Symplify\PhpConfigPrinter\Printer\SmartPhpConfigPrinter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ConfigFileFactory
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var SmartPhpConfigPrinter
     */
    private $smartPhpConfigPrinter;

    /**
     * @var RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;

    public function __construct(
        ValueResolver $valueResolver,
        PhpConfigPrinterFactory $phpConfigPrinterFactory,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector
    ) {
        $this->valueResolver = $valueResolver;
        $this->smartPhpConfigPrinter = $phpConfigPrinterFactory->create();
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }

    public function createConfigFile(ClassMethod $getRectorClassMethod): void
    {
        $onlyStmt = $getRectorClassMethod->stmts[0] ?? null;
        if (! $onlyStmt instanceof Return_) {
            throw new ShouldNotHappenException();
        }

        if (! $onlyStmt->expr instanceof ClassConstFetch) {
            throw new ShouldNotHappenException();
        }

        $rectorClass = $this->valueResolver->getValue($onlyStmt->expr);
        if (! is_string($rectorClass)) {
            throw new ShouldNotHappenException();
        }

        $phpConfigFileContent = $this->smartPhpConfigPrinter->printConfiguredServices([
            $rectorClass => null,
        ]);

        $fileInfo = $getRectorClassMethod->getAttribute(AttributeKey::FILE_INFO);
        if (! $fileInfo instanceof SmartFileInfo) {
            throw new ShouldNotHappenException();
        }

        $configFilePath = dirname($fileInfo->getRealPath()) . '/config/configured_rule.php';
        $addedFileWithContent = new AddedFileWithContent($configFilePath, $phpConfigFileContent);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
    }
}

<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Printer;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class FactoryClassPrinter
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        SmartFileSystem $smartFileSystem,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->smartFileSystem = $smartFileSystem;
    }

    public function printFactoryForClass(Class_ $factoryClass, Class_ $oldClass): void
    {
        $parentNode = $oldClass->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Namespace_) {
            $newNamespace = clone $parentNode;
            $newNamespace->stmts = [];
            $newNamespace->stmts[] = $factoryClass;
            $nodeToPrint = $newNamespace;
        } else {
            $nodeToPrint = $factoryClass;
        }

        $factoryClassFilePath = $this->createFactoryClassFilePath($oldClass);
        $factoryClassContent = $this->betterStandardPrinter->prettyPrintFile([$nodeToPrint]);

        $this->smartFileSystem->dumpFile($factoryClassFilePath, $factoryClassContent);
    }

    private function createFactoryClassFilePath(Class_ $oldClass): string
    {
        $classFileInfo = $oldClass->getAttribute(AttributeKey::FILE_INFO);
        if (! $classFileInfo instanceof SmartFileInfo) {
            throw new ShouldNotHappenException();
        }

        $directoryPath = Strings::before($classFileInfo->getRealPath(), DIRECTORY_SEPARATOR, -1);
        $resolvedOldClass = $this->nodeNameResolver->getName($oldClass);
        if ($resolvedOldClass === null) {
            throw new ShouldNotHappenException();
        }

        $bareClassName = Strings::after($resolvedOldClass, '\\', -1) . 'Factory.php';

        return $directoryPath . DIRECTORY_SEPARATOR . $bareClassName;
    }
}

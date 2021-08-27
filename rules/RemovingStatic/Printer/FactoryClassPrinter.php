<?php

declare (strict_types=1);
namespace Rector\RemovingStatic\Printer;

use RectorPrefix20210827\Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20210827\Symplify\SmartFileSystem\SmartFileSystem;
final class FactoryClassPrinter
{
    /**
     * @var \Rector\Core\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(\Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter, \RectorPrefix20210827\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->smartFileSystem = $smartFileSystem;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->currentFileProvider = $currentFileProvider;
    }
    public function printFactoryForClass(\PhpParser\Node\Stmt\Class_ $factoryClass, \PhpParser\Node\Stmt\Class_ $oldClass) : void
    {
        $parentNode = $oldClass->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode instanceof \PhpParser\Node\Stmt\Namespace_) {
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
    private function createFactoryClassFilePath(\PhpParser\Node\Stmt\Class_ $oldClass) : string
    {
        $file = $this->currentFileProvider->getFile();
        $smartFileInfo = $file->getSmartFileInfo();
        $directoryPath = \RectorPrefix20210827\Nette\Utils\Strings::before($smartFileInfo->getRealPath(), \DIRECTORY_SEPARATOR, -1);
        $resolvedOldClass = $this->nodeNameResolver->getName($oldClass);
        if ($resolvedOldClass === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $bareClassName = \RectorPrefix20210827\Nette\Utils\Strings::after($resolvedOldClass, '\\', -1) . 'Factory.php';
        return $directoryPath . \DIRECTORY_SEPARATOR . $bareClassName;
    }
}

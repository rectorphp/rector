<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Printer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class FactoryClassPrinter
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        NameResolver $nameResolver,
        BetterStandardPrinter $betterStandardPrinter,
        Filesystem $filesystem
    ) {
        $this->nameResolver = $nameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->filesystem = $filesystem;
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
        $factoryClassContent = $this->rawPrintNode($nodeToPrint);

        $this->filesystem->dumpFile($factoryClassFilePath, $factoryClassContent);
    }

    /**
     * @param Node|Node[] $node
     */
    private function rawPrintNode($node): string
    {
        return sprintf('<?php%s%s%s', PHP_EOL . PHP_EOL, $this->betterStandardPrinter->print($node), PHP_EOL);
    }

    private function createFactoryClassFilePath(Class_ $oldClass): string
    {
        /** @var SmartFileInfo|null $classFileInfo */
        $classFileInfo = $oldClass->getAttribute(AttributeKey::FILE_INFO);
        if ($classFileInfo === null) {
            throw new ShouldNotHappenException();
        }

        $directoryPath = Strings::before($classFileInfo->getRealPath(), DIRECTORY_SEPARATOR, -1);
        $bareClassName = Strings::after($this->nameResolver->resolve($oldClass), '\\', -1) . 'Factory.php';

        return $directoryPath . DIRECTORY_SEPARATOR . $bareClassName;
    }
}

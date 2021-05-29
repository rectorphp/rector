<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use Rector\CodingStyle\Application\UseImportsAdder;
use Rector\CodingStyle\Application\UseImportsRemover;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseAddingPostRector extends AbstractPostRector
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private TypeFactory $typeFactory,
        private UseImportsAdder $useImportsAdder,
        private UseImportsRemover $useImportsRemover,
        private UseNodesToAddCollector $useNodesToAddCollector,
        private CurrentFileProvider $currentFileProvider
    ) {
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes): array
    {
        // no nodes → just return
        if ($nodes === []) {
            return $nodes;
        }

        $file = $this->currentFileProvider->getFile();
        $smartFileInfo = $file->getSmartFileInfo();

        $useImportTypes = $this->useNodesToAddCollector->getObjectImportsByFileInfo($smartFileInfo);
        $functionUseImportTypes = $this->useNodesToAddCollector->getFunctionImportsByFileInfo($smartFileInfo);
        $removedShortUses = $this->useNodesToAddCollector->getShortUsesByFileInfo($smartFileInfo);

        // nothing to import or remove
        if ($useImportTypes === [] && $functionUseImportTypes === [] && $removedShortUses === []) {
            return $nodes;
        }

        /** @var FullyQualifiedObjectType[] $useImportTypes */
        $useImportTypes = $this->typeFactory->uniquateTypes($useImportTypes);

        $this->useNodesToAddCollector->clear($smartFileInfo);

        // A. has namespace? add under it
        $namespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if ($namespace instanceof Namespace_) {
            // first clean
            $this->useImportsRemover->removeImportsFromNamespace($namespace, $removedShortUses);
            // then add, to prevent adding + removing false positive of same short use
            $this->useImportsAdder->addImportsToNamespace($namespace, $useImportTypes, $functionUseImportTypes);

            return $nodes;
        }

        $firstNode = $nodes[0];
        if ($firstNode instanceof FileWithoutNamespace) {
            $nodes = $firstNode->stmts;
        }

        // B. no namespace? add in the top
        // first clean
        $nodes = $this->useImportsRemover->removeImportsFromStmts($nodes, $removedShortUses);
        $useImportTypes = $this->filterOutNonNamespacedNames($useImportTypes);
        // then add, to prevent adding + removing false positive of same short use

        return $this->useImportsAdder->addImportsToStmts($nodes, $useImportTypes, $functionUseImportTypes);
    }

    public function getPriority(): int
    {
        // must be after name importing
        return 500;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add unique use imports collected during Rector run',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(AnotherClass $anotherClass)
    {
    }
}
CODE_SAMPLE
                        ,
                    <<<'CODE_SAMPLE'
use App\AnotherClass;

class SomeClass
{
    public function run(AnotherClass $anotherClass)
    {
    }
}
CODE_SAMPLE
                ), ]
        );
    }

    /**
     * Prevents
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @return FullyQualifiedObjectType[]
     */
    private function filterOutNonNamespacedNames(array $useImportTypes): array
    {
        $namespacedUseImportTypes = [];

        foreach ($useImportTypes as $useImportType) {
            if (! \str_contains($useImportType->getClassName(), '\\')) {
                continue;
            }

            $namespacedUseImportTypes[] = $useImportType;
        }

        return $namespacedUseImportTypes;
    }
}

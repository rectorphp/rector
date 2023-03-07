<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use Rector\CodingStyle\Application\UseImportsAdder;
use Rector\CodingStyle\Application\UseImportsRemover;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class UseAddingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\CodingStyle\Application\UseImportsAdder
     */
    private $useImportsAdder;
    /**
     * @readonly
     * @var \Rector\CodingStyle\Application\UseImportsRemover
     */
    private $useImportsRemover;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\UseNodesToAddCollector
     */
    private $useNodesToAddCollector;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    public function __construct(BetterNodeFinder $betterNodeFinder, TypeFactory $typeFactory, UseImportsAdder $useImportsAdder, UseImportsRemover $useImportsRemover, UseNodesToAddCollector $useNodesToAddCollector, CurrentFileProvider $currentFileProvider, RenamedClassesDataCollector $renamedClassesDataCollector)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->typeFactory = $typeFactory;
        $this->useImportsAdder = $useImportsAdder;
        $this->useImportsRemover = $useImportsRemover;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->currentFileProvider = $currentFileProvider;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes) : array
    {
        // no nodes → just return
        if ($nodes === []) {
            return $nodes;
        }
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            throw new ShouldNotHappenException();
        }
        $useImportTypes = $this->useNodesToAddCollector->getObjectImportsByFilePath($file->getFilePath());
        $functionUseImportTypes = $this->useNodesToAddCollector->getFunctionImportsByFilePath($file->getFilePath());
        $removedUses = $this->renamedClassesDataCollector->getOldClasses();
        // nothing to import or remove
        if ($useImportTypes === [] && $functionUseImportTypes === [] && $removedUses === []) {
            return $nodes;
        }
        /** @var FullyQualifiedObjectType[] $useImportTypes */
        $useImportTypes = $this->typeFactory->uniquateTypes($useImportTypes);
        $firstNode = $nodes[0];
        if ($firstNode instanceof FileWithoutNamespace) {
            $nodes = $firstNode->stmts;
        }
        $namespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if (!$firstNode instanceof FileWithoutNamespace && !$namespace instanceof Namespace_) {
            return $nodes;
        }
        if ($namespace instanceof Namespace_) {
            // clean namespace stmts, don't assign, this used to clean the stmts of Namespace_
            $this->useImportsRemover->removeImportsFromStmts($namespace->stmts, $removedUses);
        }
        if ($firstNode instanceof FileWithoutNamespace) {
            // clean no-namespace stmts, assign
            $nodes = $this->useImportsRemover->removeImportsFromStmts($nodes, $removedUses);
        }
        return $this->resolveNodesWithImportedUses($nodes, $useImportTypes, $functionUseImportTypes, $namespace);
    }
    /**
     * @param Stmt[] $nodes
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @param FullyQualifiedObjectType[] $functionUseImportTypes
     * @return Stmt[]
     */
    private function resolveNodesWithImportedUses(array $nodes, array $useImportTypes, array $functionUseImportTypes, ?Namespace_ $namespace) : array
    {
        // A. has namespace? add under it
        if ($namespace instanceof Namespace_) {
            // then add, to prevent adding + removing false positive of same short use
            $this->useImportsAdder->addImportsToNamespace($namespace, $useImportTypes, $functionUseImportTypes);
            return $nodes;
        }
        // B. no namespace? add in the top
        $useImportTypes = $this->filterOutNonNamespacedNames($useImportTypes);
        // then add, to prevent adding + removing false positive of same short use
        return $this->useImportsAdder->addImportsToStmts($nodes, $useImportTypes, $functionUseImportTypes);
    }
    public function getPriority() : int
    {
        // must be after name importing
        return 500;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add unique use imports collected during Rector run', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(AnotherClass $anotherClass)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use App\AnotherClass;

class SomeClass
{
    public function run(AnotherClass $anotherClass)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * Prevents
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @return FullyQualifiedObjectType[]
     */
    private function filterOutNonNamespacedNames(array $useImportTypes) : array
    {
        $namespacedUseImportTypes = [];
        foreach ($useImportTypes as $useImportType) {
            if (\strpos($useImportType->getClassName(), '\\') === \false) {
                continue;
            }
            $namespacedUseImportTypes[] = $useImportType;
        }
        return $namespacedUseImportTypes;
    }
}

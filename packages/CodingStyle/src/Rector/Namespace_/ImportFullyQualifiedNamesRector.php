<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\CodingStyle\Imports\AliasUsesResolver;
use Rector\CodingStyle\Imports\ImportSkipper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\ImportFullyQualifiedNamesRectorTest
 */
final class ImportFullyQualifiedNamesRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $aliasedUses = [];

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var bool
     */
    private $shouldImportDocBlocks = true;

    /**
     * @var AliasUsesResolver
     */
    private $aliasUsesResolver;

    /**
     * @var UseAddingCommander
     */
    private $useAddingCommander;

    /**
     * @var ImportSkipper
     */
    private $importSkipper;

    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        AliasUsesResolver $aliasUsesResolver,
        UseAddingCommander $useAddingCommander,
        ImportSkipper $importSkipper,
        bool $shouldImportDocBlocks = true
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->shouldImportDocBlocks = $shouldImportDocBlocks;
        $this->useAddingCommander = $useAddingCommander;
        $this->aliasUsesResolver = $aliasUsesResolver;
        $this->importSkipper = $importSkipper;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Import fully qualified names to use statements', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function create()
    {
          return SomeAnother\AnotherClass;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use SomeAnother\AnotherClass;

class SomeClass
{
    public function create()
    {
          return AnotherClass;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Name::class, Node::class];
    }

    /**
     * @param Name|Node $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->useAddingCommander->analyseFileInfoUseStatements($node);

        if ($node instanceof Name) {
            $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node);
            if (! $staticType instanceof FullyQualifiedObjectType) {
                return null;
            }

            if ($this->isNamespaceOrUseImportName($node)) {
                return null;
            }

            $this->aliasedUses = $this->aliasUsesResolver->resolveForNode($node);

            return $this->importNameAndCollectNewUseStatement($node, $staticType);
        }

        // process doc blocks
        if ($this->shouldImportDocBlocks) {
            $this->docBlockManipulator->importNames($node);
            return $node;
        }

        return null;
    }

    private function importNameAndCollectNewUseStatement(
        Name $name,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): ?Name {
        // the same end is already imported → skip
        if ($this->importSkipper->shouldSkipName($name, $fullyQualifiedObjectType)) {
            return null;
        }

        if ($this->useAddingCommander->isShortImported($name, $fullyQualifiedObjectType)) {
            if ($this->useAddingCommander->isImportShortable($name, $fullyQualifiedObjectType)) {
                return $fullyQualifiedObjectType->getShortNameNode();
            }

            return null;
        }

        if (! $this->useAddingCommander->hasImport($name, $fullyQualifiedObjectType)) {
            $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof FuncCall) {
                $this->useAddingCommander->addFunctionUseImport($name, $fullyQualifiedObjectType);
            } else {
                $this->useAddingCommander->addUseImport($name, $fullyQualifiedObjectType);
            }
        }

        // possibly aliased
        foreach ($this->aliasedUses as $aliasUse) {
            if ($fullyQualifiedObjectType->getClassName() === $aliasUse) {
                return null;
            }
        }

        return $fullyQualifiedObjectType->getShortNameNode();
    }

    /**
     * Skip:
     * - namespace name
     * - use import name
     */
    private function isNamespaceOrUseImportName(Name $name): bool
    {
        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Namespace_) {
            return true;
        }

        return $parentNode instanceof UseUse;
    }
}

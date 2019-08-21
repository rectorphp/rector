<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Namespace_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\CodingStyle\Imports\AliasUsesResolver;
use Rector\CodingStyle\Imports\ShortNameResolver;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ImportFullyQualifiedNamesRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $shortNames = [];

    /**
     * @var string[]
     */
    private $aliasedUses = [];

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var ClassNaming
     */
    private $classNaming;

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
     * @var ShortNameResolver
     */
    private $shortNameResolver;

    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        ClassNaming $classNaming,
        AliasUsesResolver $aliasUsesResolver,
        UseAddingCommander $useAddingCommander,
        ShortNameResolver $shortNameResolver,
        bool $shouldImportDocBlocks = true
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->classNaming = $classNaming;
        $this->shouldImportDocBlocks = $shouldImportDocBlocks;
        $this->useAddingCommander = $useAddingCommander;
        $this->aliasUsesResolver = $aliasUsesResolver;
        $this->shortNameResolver = $shortNameResolver;
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
            if (! $this->canBeNameImported($node)) {
                return null;
            }

            $this->aliasedUses = $this->aliasUsesResolver->resolveForNode($node);

            // "new X" or "X::static()"
            $this->shortNames = $this->shortNameResolver->resolveForNode($node);

            return $this->importNamesAndCollectNewUseStatements($node);
        }

        // process every doc block node
        if ($this->shouldImportDocBlocks) {
            $useImports = $this->docBlockManipulator->importNames($node);
            foreach ($useImports as $useImport) {
                $this->useAddingCommander->addUseImport($node, $useImport);
            }

            return $node;
        }

        return null;
    }

    private function importNamesAndCollectNewUseStatements(Name $name): ?Name
    {
        $originalName = $name->getAttribute('originalName');

        if (! $originalName instanceof Name) {
            // not sure what to do
            return null;
        }

        // the short name is already used, skip it
        // @todo this is duplicated check of - $this->useAddingCommander->isShortImported?
        $shortName = $this->classNaming->getShortName($name->toString());

        if ($this->isShortNameAlreadyUsedForDifferentFqn($name, $shortName)) {
            return null;
        }

        $fullyQualifiedName = $this->getName($name);

        // the similar end is already imported → skip
        if ($this->shouldSkipName($name, $fullyQualifiedName)) {
            return null;
        }

        $shortName = $this->classNaming->getShortName($fullyQualifiedName);

        if ($this->useAddingCommander->isShortImported($name, $fullyQualifiedName)) {
            if ($this->useAddingCommander->isImportShortable($name, $fullyQualifiedName)) {
                return new Name($shortName);
            }

            return null;
        }

        if (! $this->useAddingCommander->hasImport($name, $fullyQualifiedName)) {
            if ($name->getAttribute(AttributeKey::PARENT_NODE) instanceof FuncCall) {
                $this->useAddingCommander->addFunctionUseImport($name, $fullyQualifiedName);
            } else {
                $this->useAddingCommander->addUseImport($name, $fullyQualifiedName);
            }
        }

        // possibly aliased
        if (in_array($fullyQualifiedName, $this->aliasedUses, true)) {
            return null;
        }

        return new Name($shortName);
    }

    // 1. name is fully qualified → import it
    private function shouldSkipName(Name $name, string $fullyQualifiedName): bool
    {
        $shortName = $this->classNaming->getShortName($fullyQualifiedName);

        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof ConstFetch) { // is true, false, null etc.
            return true;
        }

        if ($this->isNames($name, ['self', 'parent', 'static'])) {
            return true;
        }

        // skip native function calls
        if ($parentNode instanceof FuncCall && ! Strings::contains($fullyQualifiedName, '\\')) {
            return true;
        }

        // nothing to change
        if ($shortName === $fullyQualifiedName) {
            return false;
        }

        return $this->useAddingCommander->canImportBeAdded($name, $fullyQualifiedName);
    }

    // is already used
    private function isShortNameAlreadyUsedForDifferentFqn(Name $name, string $shortName): bool
    {
        if (! isset($this->shortNames[$shortName])) {
            return false;
        }

        return $this->shortNames[$shortName] !== $this->getName($name);
    }

    private function canBeNameImported(Name $name): bool
    {
        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);

        if ($parentNode instanceof Namespace_) {
            return false;
        }

        return ! $parentNode instanceof UseUse;
    }
}

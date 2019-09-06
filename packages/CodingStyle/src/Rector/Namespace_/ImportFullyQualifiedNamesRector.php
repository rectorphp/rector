<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Namespace_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\CodingStyle\Imports\AliasUsesResolver;
use Rector\CodingStyle\Imports\ShortNameResolver;
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
        AliasUsesResolver $aliasUsesResolver,
        UseAddingCommander $useAddingCommander,
        ShortNameResolver $shortNameResolver,
        bool $shouldImportDocBlocks = true
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
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
            $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node);
            if (! $staticType instanceof FullyQualifiedObjectType) {
                return null;
            }

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

        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($name);
        if (! $staticType instanceof FullyQualifiedObjectType) {
            return null;
        }

        // the short name is already used, skip it
        // @todo this is duplicated check of - $this->useAddingCommander->isShortImported?
        $shortName = $staticType->getShortName();

        if ($this->isShortNameAlreadyUsedForDifferentFqn($name, $shortName)) {
            return null;
        }

//        $fullyQualifiedName = $this->getName($name);

//        $fullyQualifiedObjectType = $this->staticTypeMapper->mapStringToPHPStanType($fullyQualifiedName);
        if (! $staticType instanceof FullyQualifiedObjectType) {
            return null;
        }

        // the similar end is already imported → skip
        if ($this->shouldSkipName($name, $staticType)) {
            return null;
        }

        $shortName = $staticType->getShortName();

        if ($this->useAddingCommander->isShortImported($name, $staticType)) {
            if ($this->useAddingCommander->isImportShortable($name, $staticType)) {
                return new Name($shortName);
            }

            return null;
        }

        if (! $this->useAddingCommander->hasImport($name, $staticType)) {
            $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof FuncCall) {
                $this->useAddingCommander->addFunctionUseImport($name, $staticType);
            } else {
                $this->useAddingCommander->addUseImport($name, $staticType);
            }
        }

        // possibly aliased
        foreach ($this->aliasedUses as $aliasUse) {
            if ($staticType->getClassName() === $aliasUse) {
                return null;
            }
        }

        return new Name($shortName);
    }

    // 1. name is fully qualified → import it
    private function shouldSkipName(Name $name, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $shortName = $fullyQualifiedObjectType->getShortName();

        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
//        if ($parentNode instanceof ConstFetch) { // is true, false, null etc.
//            return true;
//        }

        // skip native function calls
//        if ($parentNode instanceof FuncCall) {
//            dump($fullyQualifiedObjectType);
//            die;
        ////        } && ! Strings::contains($fullyQualifiedObjectType, '\\')) {
//            return true;
//        }


        // nothing to change
//        if ($fullyQualifiedObjectType->getShortNameType() === $fullyQualifiedObjectType) {
//            return false;
//        }

        foreach ($this->aliasedUses as $aliasedUse) {
            // its aliased, we cannot just rename it
            if (Strings::endsWith($aliasedUse, '\\' . $shortName)) {
                return true;
            }
        }

        return $this->useAddingCommander->canImportBeAdded($name, $fullyQualifiedObjectType);
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

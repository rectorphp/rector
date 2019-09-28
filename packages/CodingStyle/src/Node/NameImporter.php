<?php declare(strict_types=1);

namespace Rector\CodingStyle\Node;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\CodingStyle\Imports\AliasUsesResolver;
use Rector\CodingStyle\Imports\ImportSkipper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\StaticTypeMapper;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class NameImporter
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var AliasUsesResolver
     */
    private $aliasUsesResolver;

    /**
     * @var string[]
     */
    private $aliasedUses = [];

    /**
     * @var UseAddingCommander
     */
    private $useAddingCommander;

    /**
     * @var ImportSkipper
     */
    private $importSkipper;

    public function __construct(
        StaticTypeMapper $staticTypeMapper,
        AliasUsesResolver $aliasUsesResolver,
        UseAddingCommander $useAddingCommander,
        ImportSkipper $importSkipper
    ) {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->aliasUsesResolver = $aliasUsesResolver;
        $this->useAddingCommander = $useAddingCommander;
        $this->importSkipper = $importSkipper;
    }

    public function importName(Name $name): ?Name
    {
        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($name);
        if (! $staticType instanceof FullyQualifiedObjectType) {
            return null;
        }

        if ($this->isNamespaceOrUseImportName($name)) {
            return null;
        }

        $this->aliasedUses = $this->aliasUsesResolver->resolveForNode($name);

        return $this->importNameAndCollectNewUseStatement($name, $staticType);
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

        $this->addUseImport($name, $fullyQualifiedObjectType);

        // possibly aliased
        foreach ($this->aliasedUses as $aliasUse) {
            if ($fullyQualifiedObjectType->getClassName() === $aliasUse) {
                return null;
            }
        }

        return $fullyQualifiedObjectType->getShortNameNode();
    }

    private function addUseImport(Name $name, FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        if ($this->useAddingCommander->hasImport($name, $fullyQualifiedObjectType)) {
            return;
        }

        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof FuncCall) {
            $this->useAddingCommander->addFunctionUseImport($name, $fullyQualifiedObjectType);
        } else {
            $this->useAddingCommander->addUseImport($name, $fullyQualifiedObjectType);
        }
    }
}

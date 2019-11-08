<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\Ast\PhpDocNodeTraverser;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\CodingStyle\Imports\ImportSkipper;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\StaticTypeMapper;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;

final class DocBlockNameImporter
{
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var UseAddingCommander
     */
    private $useAddingCommander;

    /**
     * @var bool
     */
    private $hasPhpDocChanged = false;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var bool[][]
     */
    private $usedShortNameByClasses = [];

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var ImportSkipper
     */
    private $importSkipper;

    public function __construct(
        PhpDocNodeTraverser $phpDocNodeTraverser,
        StaticTypeMapper $staticTypeMapper,
        UseAddingCommander $useAddingCommander,
        NameResolver $nameResolver,
        BetterStandardPrinter $betterStandardPrinter,
        ImportSkipper $importSkipper
    ) {
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->useAddingCommander = $useAddingCommander;
        $this->nameResolver = $nameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->importSkipper = $importSkipper;
    }

    public function importNames(
        PhpDocInfo $phpDocInfo,
        Node $phpParserNode,
        bool $shouldImportRootNamespaceClasses = true
    ): bool {
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        $this->hasPhpDocChanged = false;

        $this->phpDocNodeTraverser->traverseWithCallable($phpDocNode, function (PhpDocParserNode $docNode) use (
            $phpParserNode,
            $shouldImportRootNamespaceClasses
        ): PhpDocParserNode {
            if (! $docNode instanceof IdentifierTypeNode) {
                return $docNode;
            }

            $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($docNode, $phpParserNode);
            if (! $staticType instanceof FullyQualifiedObjectType) {
                return $docNode;
            }

            // Importing root namespace classes (like \DateTime) is optional
            if (! $shouldImportRootNamespaceClasses && substr_count($staticType->getClassName(), '\\') === 0) {
                return $docNode;
            }

            return $this->processFqnNameImport($phpParserNode, $docNode, $staticType);
        });

        return $this->hasPhpDocChanged;
    }

    private function processFqnNameImport(
        Node $node,
        IdentifierTypeNode $identifierTypeNode,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): PhpDocParserNode {
        // nothing to be changed → skip
        if ($this->hasTheSameShortClassInCurrentNamespace($node, $fullyQualifiedObjectType)) {
            return $identifierTypeNode;
        }

        if ($this->importSkipper->shouldSkipNameForFullyQualifiedObjectType($node, $fullyQualifiedObjectType)) {
            return $identifierTypeNode;
        }

        if ($this->useAddingCommander->isShortImported($node, $fullyQualifiedObjectType)) {
            if ($this->useAddingCommander->isImportShortable($node, $fullyQualifiedObjectType)) {
                $identifierTypeNode->name = $fullyQualifiedObjectType->getShortName();
                $this->hasPhpDocChanged = true;
            }

            return $identifierTypeNode;
        }

        $identifierTypeNode->name = $fullyQualifiedObjectType->getShortName();
        $this->hasPhpDocChanged = true;
        $this->useAddingCommander->addUseImport($node, $fullyQualifiedObjectType);

        return $identifierTypeNode;
    }

    /**
     * The class in the same namespace as different file can se used in this code, the short names would colide → skip
     *
     * E.g. this namespace:
     * App\Product
     *
     * And the FQN:
     * App\SomeNesting\Product
     */
    private function hasTheSameShortClassInCurrentNamespace(
        Node $node,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        // the name is already in the same namespace implicitly
        $namespaceName = $node->getAttribute(AttributeKey::NAMESPACE_NAME);
        $currentNamespaceShortName = $namespaceName . '\\' . $fullyQualifiedObjectType->getShortName();

        if (ClassExistenceStaticHelper::doesClassLikeExist($currentNamespaceShortName)) {
            return false;
        }

        if ($currentNamespaceShortName === $fullyQualifiedObjectType->getClassName()) {
            return false;
        }

        return $this->isCurrentNamespaceSameShortClassAlreadyUsed(
            $node,
            $currentNamespaceShortName,
            $fullyQualifiedObjectType->getShortNameType()
        );
    }

    private function isCurrentNamespaceSameShortClassAlreadyUsed(
        Node $node,
        string $fullyQualifiedName,
        ShortenedObjectType $shortenedObjectType
    ): bool {
        /** @var ClassLike|null $classNode */
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            // cannot say, so rather yes
            return true;
        }

        $className = $this->nameResolver->getName($classNode);

        if (isset($this->usedShortNameByClasses[$className][$shortenedObjectType->getShortName()])) {
            return $this->usedShortNameByClasses[$className][$shortenedObjectType->getShortName()];
        }

        $printedClass = $this->betterStandardPrinter->print($classNode->stmts);

        // short with space " Type"| fqn
        $shortNameOrFullyQualifiedNamePattern = sprintf(
            '#(\s%s\b|\b%s\b)#',
            preg_quote($shortenedObjectType->getShortName(), '#'),
            preg_quote($fullyQualifiedName, '#')
        );

        $isShortClassUsed = (bool) Strings::match($printedClass, $shortNameOrFullyQualifiedNamePattern);

        $this->usedShortNameByClasses[$className][$shortenedObjectType->getShortName()] = $isShortClassUsed;

        return $isShortClassUsed;
    }
}

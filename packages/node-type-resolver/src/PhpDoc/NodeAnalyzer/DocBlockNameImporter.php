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
use Rector\Core\Configuration\Option;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\StaticTypeMapper;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class DocBlockNameImporter
{
    /**
     * @var bool
     */
    private $hasPhpDocChanged = false;

    /**
     * @var bool[][]
     */
    private $usedShortNameByClasses = [];

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
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var ImportSkipper
     */
    private $importSkipper;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(
        PhpDocNodeTraverser $phpDocNodeTraverser,
        StaticTypeMapper $staticTypeMapper,
        UseAddingCommander $useAddingCommander,
        NodeNameResolver $nodeNameResolver,
        BetterStandardPrinter $betterStandardPrinter,
        ImportSkipper $importSkipper,
        ParameterProvider $parameterProvider
    ) {
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->useAddingCommander = $useAddingCommander;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->importSkipper = $importSkipper;
        $this->parameterProvider = $parameterProvider;
    }

    public function importNames(PhpDocInfo $phpDocInfo, Node $phpParserNode): bool
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        $this->hasPhpDocChanged = false;

        $this->phpDocNodeTraverser->traverseWithCallable($phpDocNode, function (PhpDocParserNode $docNode) use (
            $phpParserNode
        ): PhpDocParserNode {
            if (! $docNode instanceof IdentifierTypeNode) {
                return $docNode;
            }

            $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($docNode, $phpParserNode);
            if (! $staticType instanceof FullyQualifiedObjectType) {
                return $docNode;
            }

            $importShortClasses = $this->parameterProvider->provideParameter(Option::IMPORT_SHORT_CLASSES_PARAMETER);
            // Importing root namespace classes (like \DateTime) is optional
            if (! $importShortClasses && substr_count($staticType->getClassName(), '\\') === 0) {
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

        $className = $this->nodeNameResolver->getName($classNode);

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

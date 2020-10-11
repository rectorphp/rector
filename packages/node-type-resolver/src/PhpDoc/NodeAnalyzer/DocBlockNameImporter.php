<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\CodingStyle\Imports\ImportSkipper;
use Rector\Core\Configuration\Option;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\SimplePhpDocParser\PhpDocNodeTraverser;
use Rector\StaticTypeMapper\StaticTypeMapper;
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

    /**
     * @var UseNodesToAddCollector
     */
    private $useNodesToAddCollector;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        ImportSkipper $importSkipper,
        NodeNameResolver $nodeNameResolver,
        ParameterProvider $parameterProvider,
        PhpDocNodeTraverser $phpDocNodeTraverser,
        StaticTypeMapper $staticTypeMapper,
        UseNodesToAddCollector $useNodesToAddCollector
    ) {
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->importSkipper = $importSkipper;
        $this->parameterProvider = $parameterProvider;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }

    public function importNames(PhpDocInfo $phpDocInfo, Node $phpParserNode): bool
    {
        $attributeAwarePhpDocNode = $phpDocInfo->getPhpDocNode();

        $this->hasPhpDocChanged = false;

        $this->phpDocNodeTraverser->traverseWithCallable($attributeAwarePhpDocNode, '', function (
            PhpDocParserNode $docNode
        ) use ($phpParserNode): PhpDocParserNode {
            if (! $docNode instanceof IdentifierTypeNode) {
                return $docNode;
            }

            $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($docNode, $phpParserNode);
            if (! $staticType instanceof FullyQualifiedObjectType) {
                return $docNode;
            }

            $importShortClasses = $this->parameterProvider->provideParameter(Option::IMPORT_SHORT_CLASSES);

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

        // should skip because its already used
        if ($this->useNodesToAddCollector->isShortImported($node, $fullyQualifiedObjectType)) {
            if ($this->useNodesToAddCollector->isImportShortable($node, $fullyQualifiedObjectType)) {
                $identifierTypeNode->name = $fullyQualifiedObjectType->getShortName();
                $this->hasPhpDocChanged = true;
            }

            return $identifierTypeNode;
        }

        $shortenedIdentifierTypeNode = new IdentifierTypeNode($fullyQualifiedObjectType->getShortName());

        $this->hasPhpDocChanged = true;
        $this->useNodesToAddCollector->addUseImport($node, $fullyQualifiedObjectType);

        return $shortenedIdentifierTypeNode;
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
        /** @var ClassLike|null $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            // cannot say, so rather yes
            return true;
        }

        $className = $this->nodeNameResolver->getName($classLike);

        if (isset($this->usedShortNameByClasses[$className][$shortenedObjectType->getShortName()])) {
            return $this->usedShortNameByClasses[$className][$shortenedObjectType->getShortName()];
        }

        $printedClass = $this->betterStandardPrinter->print($classLike->stmts);

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

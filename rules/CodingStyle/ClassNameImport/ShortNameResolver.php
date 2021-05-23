<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ClassNameImport;

use Nette\Utils\Reflection;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;
use Symfony\Contracts\Service\Attribute\Required;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ShortNameResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/KphLd2/1
     */
    private const BIG_LETTER_START_REGEX = '#^[A-Z]#';

    /**
     * @var string[][]
     */
    private array $shortNamesByFilePath = [];

    private PhpDocInfoFactory $phpDocInfoFactory;

    public function __construct(
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private NodeNameResolver $nodeNameResolver,
        private NodeFinder $nodeFinder,
        private ReflectionProvider $reflectionProvider,
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    // Avoids circular reference

    #[Required]
    public function autowireShortNameResolver(PhpDocInfoFactory $phpDocInfoFactory): void
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    /**
     * @return array<string, string>
     */
    public function resolveForNode(File $file): array
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $nodeRealPath = $smartFileInfo->getRealPath();

        if (isset($this->shortNamesByFilePath[$nodeRealPath])) {
            return $this->shortNamesByFilePath[$nodeRealPath];
        }

        $shortNamesToFullyQualifiedNames = $this->resolveForStmts($file->getNewStmts());
        $this->shortNamesByFilePath[$nodeRealPath] = $shortNamesToFullyQualifiedNames;

        return $shortNamesToFullyQualifiedNames;
    }

    /**
     * Collects all "class <SomeClass>", "trait <SomeTrait>" and "interface <SomeInterface>"
     * @return string[]
     */
    public function resolveShortClassLikeNamesForNode(Node $node): array
    {
        $namespace = $this->betterNodeFinder->findParentType($node, Namespace_::class);
        if (! $namespace instanceof Namespace_) {
            // only handle namespace nodes
            return [];
        }

        /** @var ClassLike[] $classLikes */
        $classLikes = $this->nodeFinder->findInstanceOf($namespace, ClassLike::class);

        $shortClassLikeNames = [];
        foreach ($classLikes as $classLike) {
            $shortClassLikeNames[] = $this->nodeNameResolver->getShortName($classLike);
        }

        return array_unique($shortClassLikeNames);
    }

    /**
     * @param Node[] $stmts
     * @return array<string, string>
     */
    private function resolveForStmts(array $stmts): array
    {
        $shortNamesToFullyQualifiedNames = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use (
            &$shortNamesToFullyQualifiedNames
        ): void {
            // class name is used!
            if ($node instanceof ClassLike && $node->name instanceof Identifier) {
                $fullyQualifiedName = $this->nodeNameResolver->getName($node);
                if ($fullyQualifiedName === null) {
                    return;
                }

                $shortNamesToFullyQualifiedNames[$node->name->toString()] = $fullyQualifiedName;
                return;
            }

            if (! $node instanceof Name) {
                return;
            }

            $originalName = $node->getAttribute(AttributeKey::ORIGINAL_NAME);
            if (! $originalName instanceof Name) {
                return;
            }

            // already short
            if (Strings::contains($originalName->toString(), '\\')) {
                return;
            }

            $fullyQualifiedName = $this->nodeNameResolver->getName($node);
            $shortNamesToFullyQualifiedNames[$originalName->toString()] = $fullyQualifiedName;
        });

        $docBlockShortNamesToFullyQualifiedNames = $this->resolveFromDocBlocks($stmts);
        return array_merge($shortNamesToFullyQualifiedNames, $docBlockShortNamesToFullyQualifiedNames);
    }

    /**
     * @param Node[] $stmts
     * @return array<string, string>
     */
    private function resolveFromDocBlocks(array $stmts): array
    {
        $reflectionClass = $this->resolveNativeClassReflection($stmts);

        $shortNamesToFullyQualifiedNames = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use (
            &$shortNamesToFullyQualifiedNames,
            $reflectionClass
        ): void {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

            foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
                /** @var PhpDocChildNode $phpDocChildNode */
                $shortTagName = $this->resolveShortTagNameFromPhpDocChildNode($phpDocChildNode);
                if ($shortTagName === null) {
                    continue;
                }

                if ($reflectionClass !== null) {
                    $fullyQualifiedTagName = Reflection::expandClassName($shortTagName, $reflectionClass);
                } else {
                    $fullyQualifiedTagName = $shortTagName;
                }

                $shortNamesToFullyQualifiedNames[$shortTagName] = $fullyQualifiedTagName;
            }
        });

        return $shortNamesToFullyQualifiedNames;
    }

    private function resolveShortTagNameFromPhpDocChildNode(PhpDocChildNode $phpDocChildNode): ?string
    {
        if (! $phpDocChildNode instanceof PhpDocTagNode) {
            return null;
        }

        $tagName = ltrim($phpDocChildNode->name, '@');

        // is annotation class - big letter?
        if (Strings::match($tagName, self::BIG_LETTER_START_REGEX)) {
            return $tagName;
        }

        if (! $this->isValueNodeWithType($phpDocChildNode->value)) {
            return null;
        }

        $typeNode = $phpDocChildNode->value->type;
        if (! $typeNode instanceof IdentifierTypeNode) {
            return null;
        }

        if (Strings::contains($typeNode->name, '\\')) {
            return null;
        }

        return $typeNode->name;
    }

    private function isValueNodeWithType(PhpDocTagValueNode $phpDocTagValueNode): bool
    {
        return $phpDocTagValueNode instanceof PropertyTagValueNode ||
            $phpDocTagValueNode instanceof ReturnTagValueNode ||
            $phpDocTagValueNode instanceof ParamTagValueNode ||
            $phpDocTagValueNode instanceof VarTagValueNode ||
            $phpDocTagValueNode instanceof ThrowsTagValueNode;
    }

    /**
     * @param Node[] $stmts
     */
    private function resolveNativeClassReflection(array $stmts): ?ReflectionClass
    {
        $firstClassLike = $this->nodeFinder->findFirstInstanceOf($stmts, ClassLike::class);
        if (! $firstClassLike instanceof ClassLike) {
            return null;
        }

        $className = $this->nodeNameResolver->getName($firstClassLike);
        if (! $className) {
            return null;
        }

        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        return $classReflection->getNativeReflection();
    }
}

<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ClassNameImport;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use Symplify\SmartFileSystem\SmartFileInfo;

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
    private $shortNamesByFilePath = [];

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        CurrentFileInfoProvider $currentFileInfoProvider,
        PhpDocInfoFactory $phpDocInfoFactory,
        ClassNaming $classNaming
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->classNaming = $classNaming;
    }

    /**
     * @return string[]
     */
    public function resolveForNode(Node $node): array
    {
        $realPath = $this->getNodeRealPath($node);

        if (isset($this->shortNamesByFilePath[$realPath])) {
            return $this->shortNamesByFilePath[$realPath];
        }

        $currentStmts = $this->currentFileInfoProvider->getCurrentStmts();

        $shortNames = $this->resolveForStmts($currentStmts);
        $this->shortNamesByFilePath[$realPath] = $shortNames;

        return $shortNames;
    }

    /**
     * Collects all "class <SomeClass>", "trait <SomeTrait>" and "interface <SomeInterface>"
     * @return string[]
     */
    public function resolveShortClassLikeNamesForNode(Node $node): array
    {
        $namespace = $node->getAttribute(AttributeKey::NAMESPACE_NODE);
        if ($namespace === null) {
            // only handle namespace nodes
            return [];
        }

        $shortClassLikeNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($namespace, function (Node $node) use (
            &$shortClassLikeNames
        ) {
            // ...
            if (! $node instanceof ClassLike) {
                return null;
            }

            if ($node->name === null) {
                return null;
            }

            $className = $node->getAttribute(AttributeKey::CLASS_NAME);
            if (is_string($className)) {
                $shortClassLikeNames[] = $this->classNaming->getShortName($className);
            }
        });

        return array_unique($shortClassLikeNames);
    }

    private function getNodeRealPath(Node $node): ?string
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo !== null) {
            return $fileInfo->getRealPath();
        }

        $smartFileInfo = $this->currentFileInfoProvider->getSmartFileInfo();
        if ($smartFileInfo !== null) {
            return $smartFileInfo->getRealPath();
        }

        return null;
    }

    /**
     * @param Node[] $stmts
     * @return string[]
     */
    private function resolveForStmts(array $stmts): array
    {
        $shortNames = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use (
            &$shortNames
        ): void {
            // class name is used!
            if ($node instanceof ClassLike && $node->name instanceof Identifier) {
                $shortNames[$node->name->toString()] = $node->name->toString();
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

            $shortNames[$originalName->toString()] = $node->toString();
        });

        $docBlockShortNames = $this->resolveFromDocBlocks($stmts);

        return array_merge($shortNames, $docBlockShortNames);
    }

    /**
     * @param Node[] $stmts
     * @return string[]
     */
    private function resolveFromDocBlocks(array $stmts): array
    {
        $shortNames = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use (
            &$shortNames
        ): void {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

            foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
                $shortTagName = $this->resolveShortTagNameFromPhpDocChildNode($phpDocChildNode);
                if ($shortTagName === null) {
                    continue;
                }

                $shortNames[$shortTagName] = $shortTagName;
            }
        });

        return $shortNames;
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
}

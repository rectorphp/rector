<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\Node as DocNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Naming\NamespaceMatcher;
use Rector\Renaming\ValueObject\RenamedNamespace;
use Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;

final class DocBlockNamespaceRenamer
{
    public function __construct(
        private readonly NamespaceMatcher $namespaceMatcher,
        private readonly PhpDocInfoFactory $phpDocInfoFactory
    ) {
    }

    /**
     * @param array<string, string> $oldToNewNamespaces
     */
    public function renameFullyQualifiedNamespace(
        Property|ClassMethod|Function_|Expression|ClassLike|FileWithoutNamespace $node,
        array $oldToNewNamespaces
    ): ?Node {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable(
            $phpDocInfo->getPhpDocNode(),
            '',
            function (DocNode $docNode) use ($oldToNewNamespaces): ?DocNode {
                if (! $docNode instanceof IdentifierTypeNode) {
                    return null;
                }

                $name = $docNode->name;
                $trimmedName = ltrim($docNode->name, '\\');

                if ($name === $trimmedName) {
                    return null;
                }

                $renamedNamespaceValueObject = $this->namespaceMatcher->matchRenamedNamespace(
                    $trimmedName,
                    $oldToNewNamespaces
                );
                if (! $renamedNamespaceValueObject instanceof RenamedNamespace) {
                    return null;
                }

                return new IdentifierTypeNode('\\' . $renamedNamespaceValueObject->getNameInNewNamespace());
            }
        );

        if (! $phpDocInfo->hasChanged()) {
            return null;
        }

        return $node;
    }
}

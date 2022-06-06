<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Function_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Node as DocNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use RectorPrefix20220606\Rector\Naming\NamespaceMatcher;
use RectorPrefix20220606\Rector\Renaming\ValueObject\RenamedNamespace;
use RectorPrefix20220606\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
final class DocBlockNamespaceRenamer
{
    /**
     * @readonly
     * @var \Rector\Naming\NamespaceMatcher
     */
    private $namespaceMatcher;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(NamespaceMatcher $namespaceMatcher, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->namespaceMatcher = $namespaceMatcher;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @param array<string, string> $oldToNewNamespaces
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\ClassLike|\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $node
     */
    public function renameFullyQualifiedNamespace($node, array $oldToNewNamespaces) : ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function (DocNode $docNode) use($oldToNewNamespaces) : ?DocNode {
            if (!$docNode instanceof IdentifierTypeNode) {
                return null;
            }
            $trimmedName = \ltrim($docNode->name, '\\');
            if ($docNode->name === $trimmedName) {
                return null;
            }
            $renamedNamespaceValueObject = $this->namespaceMatcher->matchRenamedNamespace($trimmedName, $oldToNewNamespaces);
            if (!$renamedNamespaceValueObject instanceof RenamedNamespace) {
                return null;
            }
            return new IdentifierTypeNode('\\' . $renamedNamespaceValueObject->getNameInNewNamespace());
        });
        if (!$phpDocInfo->hasChanged()) {
            return null;
        }
        return $node;
    }
}

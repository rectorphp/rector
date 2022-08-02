<?php

declare (strict_types=1);
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
use RectorPrefix202208\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
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

<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocParser;

use RectorPrefix20220606\PhpParser\Node as PhpNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use RectorPrefix20220606\Rector\StaticTypeMapper\Naming\NameScopeFactory;
use RectorPrefix20220606\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
/**
 * Decorate node with fully qualified class name for const epxr,
 * e.g. Direction::*
 */
final class ConstExprClassNameDecorator implements PhpDocNodeDecoratorInterface
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Naming\NameScopeFactory
     */
    private $nameScopeFactory;
    /**
     * @readonly
     * @var \Symplify\Astral\PhpDocParser\PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;
    public function __construct(NameScopeFactory $nameScopeFactory, PhpDocNodeTraverser $phpDocNodeTraverser)
    {
        $this->nameScopeFactory = $nameScopeFactory;
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
    }
    public function decorate(PhpDocNode $phpDocNode, PhpNode $phpNode) : void
    {
        $this->phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', function (Node $node) use($phpNode) {
            if (!$node instanceof ConstExprNode) {
                return null;
            }
            $className = $this->resolveFullyQualifiedClass($node, $phpNode);
            if ($className === null) {
                return null;
            }
            $node->setAttribute(PhpDocAttributeKey::RESOLVED_CLASS, $className);
            return $node;
        });
    }
    private function resolveFullyQualifiedClass(ConstExprNode $constExprNode, PhpNode $phpNode) : ?string
    {
        if (!$constExprNode instanceof ConstFetchNode) {
            return null;
        }
        $nameScope = $this->nameScopeFactory->createNameScopeFromNodeWithoutTemplateTypes($phpNode);
        return $nameScope->resolveStringName($constExprNode->className);
    }
}

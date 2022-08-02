<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

use PhpParser\Node as PhpNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\StaticTypeMapper\Naming\NameScopeFactory;
use RectorPrefix202208\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
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

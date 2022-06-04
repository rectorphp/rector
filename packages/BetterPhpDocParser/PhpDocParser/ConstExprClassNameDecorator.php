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
use RectorPrefix20220604\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
/**
 * Decorate node with fully qualified class name for const epxr,
 * e.g. Direction::*
 */
final class ConstExprClassNameDecorator implements \Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface
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
    public function __construct(\Rector\StaticTypeMapper\Naming\NameScopeFactory $nameScopeFactory, \RectorPrefix20220604\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser $phpDocNodeTraverser)
    {
        $this->nameScopeFactory = $nameScopeFactory;
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
    }
    public function decorate(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode, \PhpParser\Node $phpNode) : void
    {
        $this->phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', function (\PHPStan\PhpDocParser\Ast\Node $node) use($phpNode) {
            if (!$node instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode) {
                return null;
            }
            $className = $this->resolveFullyQualifiedClass($node, $phpNode);
            if ($className === null) {
                return null;
            }
            $node->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::RESOLVED_CLASS, $className);
            return $node;
        });
    }
    private function resolveFullyQualifiedClass(\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode $constExprNode, \PhpParser\Node $phpNode) : ?string
    {
        if (!$constExprNode instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode) {
            return null;
        }
        $nameScope = $this->nameScopeFactory->createNameScopeFromNodeWithoutTemplateTypes($phpNode);
        return $nameScope->resolveStringName($constExprNode->className);
    }
}

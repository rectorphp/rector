<?php

declare (strict_types=1);
namespace Rector\Laravel\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Rector\Laravel\ValueObject\ServiceNameTypeAndVariableName;
final class AppAssignFactory
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function createAssignExpression(\Rector\Laravel\ValueObject\ServiceNameTypeAndVariableName $serviceNameTypeAndVariableName, \PhpParser\Node\Expr $expr) : \PhpParser\Node\Stmt\Expression
    {
        $variable = new \PhpParser\Node\Expr\Variable($serviceNameTypeAndVariableName->getVariableName());
        $assign = new \PhpParser\Node\Expr\Assign($variable, $expr);
        $expression = new \PhpParser\Node\Stmt\Expression($assign);
        $this->decorateWithVarAnnotation($expression, $serviceNameTypeAndVariableName);
        return $expression;
    }
    private function decorateWithVarAnnotation(\PhpParser\Node\Stmt\Expression $expression, \Rector\Laravel\ValueObject\ServiceNameTypeAndVariableName $serviceNameTypeAndVariableName) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createEmpty($expression);
        $fullyQualifiedIdentifierTypeNode = new \Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode($serviceNameTypeAndVariableName->getType());
        $varTagValueNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode($fullyQualifiedIdentifierTypeNode, '$' . $serviceNameTypeAndVariableName->getVariableName(), '');
        $phpDocInfo->addTagValueNode($varTagValueNode);
        $phpDocInfo->makeSingleLined();
    }
}

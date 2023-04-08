<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\StaticTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
final class ExprBoolCaster
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper
     */
    private $typeUnwrapper;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\StaticTypeAnalyzer
     */
    private $staticTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeTypeResolver $nodeTypeResolver, TypeUnwrapper $typeUnwrapper, StaticTypeAnalyzer $staticTypeAnalyzer, NodeFactory $nodeFactory)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeUnwrapper = $typeUnwrapper;
        $this->staticTypeAnalyzer = $staticTypeAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }
    public function boolCastOrNullCompareIfNeeded(Expr $expr) : Expr
    {
        if (!$this->nodeTypeResolver->isNullableType($expr)) {
            if (!$this->isBoolCastNeeded($expr)) {
                return $expr;
            }
            return new Bool_($expr);
        }
        $exprStaticType = $this->nodeTypeResolver->getType($expr);
        // if we remove null type, still has to be trueable
        if ($exprStaticType instanceof UnionType) {
            $unionTypeWithoutNullType = $this->typeUnwrapper->removeNullTypeFromUnionType($exprStaticType);
            if ($this->staticTypeAnalyzer->isAlwaysTruableType($unionTypeWithoutNullType)) {
                return new NotIdentical($expr, $this->nodeFactory->createNull());
            }
        } elseif ($this->staticTypeAnalyzer->isAlwaysTruableType($exprStaticType)) {
            return new NotIdentical($expr, $this->nodeFactory->createNull());
        }
        if (!$this->isBoolCastNeeded($expr)) {
            return $expr;
        }
        return new Bool_($expr);
    }
    private function isBoolCastNeeded(Expr $expr) : bool
    {
        if ($expr instanceof BooleanNot) {
            return \false;
        }
        $exprType = $this->nodeTypeResolver->getType($expr);
        if ($exprType->isBoolean()->yes()) {
            return \false;
        }
        return !$expr instanceof BinaryOp;
    }
}

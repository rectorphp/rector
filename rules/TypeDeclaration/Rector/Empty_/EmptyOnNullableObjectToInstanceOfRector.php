<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Empty_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector\EmptyOnNullableObjectToInstanceOfRectorTest
 */
final class EmptyOnNullableObjectToInstanceOfRector extends AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change empty() on nullable object to instanceof check', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(?AnotherObject $anotherObject)
    {
        if (empty($anotherObject)) {
            return false;
        }

        return true;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(?AnotherObject $anotherObject)
    {
        if (! $anotherObject instanceof AnotherObject) {
            return false;
        }

        return true;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Empty_::class, BooleanNot::class];
    }
    /**
     * @param Empty_|BooleanNot $node
     * @return null|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BooleanNot
     */
    public function refactorWithScope(Node $node, Scope $scope)
    {
        if ($node instanceof BooleanNot) {
            if (!$node->expr instanceof Empty_) {
                return null;
            }
            $isNegated = \true;
            $empty = $node->expr;
        } else {
            $empty = $node;
            $isNegated = \false;
        }
        if ($empty->expr instanceof ArrayDimFetch) {
            return null;
        }
        $exprType = $scope->getNativeType($empty->expr);
        if (!$exprType instanceof UnionType) {
            return null;
        }
        $exprType = TypeCombinator::removeNull($exprType);
        if (!$exprType instanceof ObjectType) {
            return null;
        }
        $objectType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($exprType, TypeKind::RETURN);
        if (!$objectType instanceof Name) {
            return null;
        }
        $instanceof = new Instanceof_($empty->expr, $objectType);
        if ($isNegated) {
            return $instanceof;
        }
        return new BooleanNot($instanceof);
    }
}

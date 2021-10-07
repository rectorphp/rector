<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Cast;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\Object_;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\FunctionLike;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Cast\RecastingRemovalRector\RecastingRemovalRectorTest
 */
final class RecastingRemovalRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<class-string<Node>, class-string<Type>>
     */
    private const CAST_CLASS_TO_NODE_TYPE = [\PhpParser\Node\Expr\Cast\String_::class => \PHPStan\Type\StringType::class, \PhpParser\Node\Expr\Cast\Bool_::class => \PHPStan\Type\BooleanType::class, \PhpParser\Node\Expr\Cast\Array_::class => \PHPStan\Type\ArrayType::class, \PhpParser\Node\Expr\Cast\Int_::class => \PHPStan\Type\IntegerType::class, \PhpParser\Node\Expr\Cast\Object_::class => \PHPStan\Type\ObjectType::class, \PhpParser\Node\Expr\Cast\Double::class => \PHPStan\Type\FloatType::class];
    /**
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes recasting of the same type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$string = '';
$string = (string) $string;

$array = [];
$array = (array) $array;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$string = '';
$string = $string;

$array = [];
$array = $array;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Cast::class];
    }
    /**
     * @param Cast $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $nodeClass = \get_class($node);
        if (!isset(self::CAST_CLASS_TO_NODE_TYPE[$nodeClass])) {
            return null;
        }
        $nodeType = $this->getType($node->expr);
        if ($nodeType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        $sameNodeType = self::CAST_CLASS_TO_NODE_TYPE[$nodeClass];
        if (!\is_a($nodeType, $sameNodeType, \true)) {
            return null;
        }
        if ($this->shouldSkip($node->expr)) {
            return null;
        }
        return $node->expr;
    }
    private function shouldSkip(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$this->propertyFetchAnalyzer->isPropertyFetch($expr)) {
            return $this->isNonTypedFromParam($expr);
        }
        /** @var PropertyFetch|StaticPropertyFetch $expr */
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($expr);
        if (!$phpPropertyReflection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection) {
            $propertyType = $expr instanceof \PhpParser\Node\Expr\StaticPropertyFetch ? $this->nodeTypeResolver->getType($expr->class) : $this->nodeTypeResolver->getType($expr->var);
            // need to UnionType check due rectify with RecastingRemovalRector + CountOnNullRector
            // cause add (array) cast on $node->args
            // on union $node types FuncCall|MethodCall|StaticCall
            return !$propertyType instanceof \PHPStan\Type\UnionType;
        }
        $nativeType = $phpPropertyReflection->getNativeType();
        return $nativeType instanceof \PHPStan\Type\MixedType;
    }
    private function isNonTypedFromParam(\PhpParser\Node\Expr $expr) : bool
    {
        $functionLike = $this->betterNodeFinder->findParentType($expr, \PhpParser\Node\FunctionLike::class);
        if (!$functionLike instanceof \PhpParser\Node\FunctionLike) {
            return \false;
        }
        $params = $functionLike->getParams();
        foreach ($params as $param) {
            if (!$this->nodeComparator->areNodesEqual($param->var, $expr)) {
                continue;
            }
            return $param->type === null;
        }
        return \false;
    }
}

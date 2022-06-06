<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\Rector\Cast;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Bool_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Double;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Int_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Object_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\String_;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticPropertyFetch;
use RectorPrefix20220606\PHPStan\Reflection\Php\PhpPropertyReflection;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\FloatType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\ExprAnalyzer;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\Reflection\ReflectionResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Cast\RecastingRemovalRector\RecastingRemovalRectorTest
 */
final class RecastingRemovalRector extends AbstractRector
{
    /**
     * @var array<class-string<Node>, class-string<Type>>
     */
    private const CAST_CLASS_TO_NODE_TYPE = [String_::class => StringType::class, Bool_::class => BooleanType::class, Array_::class => ArrayType::class, Int_::class => IntegerType::class, Object_::class => ObjectType::class, Double::class => FloatType::class];
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(PropertyFetchAnalyzer $propertyFetchAnalyzer, ReflectionResolver $reflectionResolver, ExprAnalyzer $exprAnalyzer)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Removes recasting of the same type', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Cast::class];
    }
    /**
     * @param Cast $node
     */
    public function refactor(Node $node) : ?Node
    {
        $nodeClass = \get_class($node);
        if (!isset(self::CAST_CLASS_TO_NODE_TYPE[$nodeClass])) {
            return null;
        }
        $nodeType = $this->getType($node->expr);
        if ($nodeType instanceof MixedType) {
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
    private function shouldSkip(Expr $expr) : bool
    {
        if (!$this->propertyFetchAnalyzer->isPropertyFetch($expr)) {
            return $this->exprAnalyzer->isNonTypedFromParam($expr);
        }
        /** @var PropertyFetch|StaticPropertyFetch $expr */
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($expr);
        if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
            $propertyType = $expr instanceof StaticPropertyFetch ? $this->nodeTypeResolver->getType($expr->class) : $this->nodeTypeResolver->getType($expr->var);
            // need to UnionType check due rectify with RecastingRemovalRector + CountOnNullRector
            // cause add (array) cast on $node->args
            // on union $node types FuncCall|MethodCall|StaticCall
            return !$propertyType instanceof UnionType;
        }
        $nativeType = $phpPropertyReflection->getNativeType();
        return $nativeType instanceof MixedType;
    }
}

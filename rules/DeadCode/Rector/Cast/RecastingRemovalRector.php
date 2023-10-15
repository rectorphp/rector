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
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Cast\RecastingRemovalRector\RecastingRemovalRectorTest
 */
final class RecastingRemovalRector extends AbstractRector
{
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
    /**
     * @var array<class-string<Node>, class-string<Type>>
     */
    private const CAST_CLASS_TO_NODE_TYPE = [String_::class => StringType::class, Bool_::class => BooleanType::class, Array_::class => ArrayType::class, Int_::class => IntegerType::class, Object_::class => ObjectType::class, Double::class => FloatType::class];
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
        if (!$nodeType instanceof $sameNodeType) {
            return null;
        }
        if ($this->shouldSkip($node->expr)) {
            return null;
        }
        if ($this->shouldSkipCall($node->expr)) {
            return null;
        }
        return $node->expr;
    }
    private function shouldSkipCall(Expr $expr) : bool
    {
        if (!$expr instanceof MethodCall && !$expr instanceof StaticCall) {
            return \false;
        }
        $type = $this->nodeTypeResolver->getNativeType($expr);
        return $type instanceof MixedType && !$type->isExplicitMixed();
    }
    private function shouldSkip(Expr $expr) : bool
    {
        if (!$this->propertyFetchAnalyzer->isPropertyFetch($expr)) {
            return $this->exprAnalyzer->isNonTypedFromParam($expr);
        }
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($expr);
        if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
            return \true;
        }
        $nativeType = $phpPropertyReflection->getNativeType();
        return $nativeType instanceof MixedType;
    }
}

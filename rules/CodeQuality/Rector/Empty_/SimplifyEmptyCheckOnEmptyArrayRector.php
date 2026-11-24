<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Empty_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\Php\ReservedKeywordAnalyzer;
use Rector\PhpParser\AstResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\AllAssignNodePropertyTypeInferer;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector\SimplifyEmptyCheckOnEmptyArrayRectorTest
 */
final class SimplifyEmptyCheckOnEmptyArrayRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ExprAnalyzer $exprAnalyzer;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private AllAssignNodePropertyTypeInferer $allAssignNodePropertyTypeInferer;
    /**
     * @readonly
     */
    private ReservedKeywordAnalyzer $reservedKeywordAnalyzer;
    /**
     * @readonly
     */
    private ConstructorAssignDetector $constructorAssignDetector;
    public function __construct(ExprAnalyzer $exprAnalyzer, ReflectionResolver $reflectionResolver, AstResolver $astResolver, AllAssignNodePropertyTypeInferer $allAssignNodePropertyTypeInferer, ReservedKeywordAnalyzer $reservedKeywordAnalyzer, ConstructorAssignDetector $constructorAssignDetector)
    {
        $this->exprAnalyzer = $exprAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->astResolver = $astResolver;
        $this->allAssignNodePropertyTypeInferer = $allAssignNodePropertyTypeInferer;
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->constructorAssignDetector = $constructorAssignDetector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Simplify empty() functions calls on empty arrays', [new CodeSample(<<<'CODE_SAMPLE'
$values = [];

if (empty($values)) {
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$values = [];

if ([] === $values) {
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Empty_::class, BooleanNot::class];
    }
    /**
     * @param Empty_|BooleanNot $node $node
     */
    public function refactor(Node $node): ?Node
    {
        $empty = $node instanceof BooleanNot ? $node->expr : $node;
        if (!$empty instanceof Empty_) {
            return null;
        }
        $expr = $empty->expr;
        if (!$expr instanceof Variable && !$expr instanceof PropertyFetch && !$expr instanceof StaticPropertyFetch) {
            return null;
        }
        if ($expr instanceof Variable && is_string($expr->name) && $this->reservedKeywordAnalyzer->isNativeVariable($expr->name)) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        if (!$this->isAllowedExpr($expr, $scope)) {
            return null;
        }
        return $node instanceof BooleanNot ? new NotIdentical($expr, new Array_()) : new Identical($expr, new Array_());
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch|\PhpParser\Node\Expr\Variable $expr
     */
    private function isAllowedExpr($expr, Scope $scope): bool
    {
        if (!$scope->getType($expr)->isArray()->yes()) {
            return \false;
        }
        if ($expr instanceof Variable) {
            return !$this->exprAnalyzer->isNonTypedFromParam($expr);
        }
        if (!$expr->name instanceof Identifier) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflectionSourceObject($expr);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $propertyName = $expr->name->toString();
        if (!$classReflection->hasNativeProperty($propertyName)) {
            return \false;
        }
        $phpPropertyReflection = $classReflection->getNativeProperty($propertyName);
        $nativeType = $phpPropertyReflection->getNativeType();
        if (!$nativeType instanceof MixedType) {
            if (!$nativeType->isArray()->yes()) {
                return \false;
            }
            // reading an uninitialized typed property throws, while empty() returns true
            return $this->isInitializedProperty($phpPropertyReflection, $propertyName);
        }
        $property = $this->astResolver->resolvePropertyFromPropertyReflection($phpPropertyReflection);
        /**
         * Skip property promotion mixed type for now, as:
         *
         *   - require assign in default param check
         *   - check all assign of property promotion params under the class
         */
        if (!$property instanceof Property) {
            return \false;
        }
        $type = $this->allAssignNodePropertyTypeInferer->inferProperty($property, $classReflection, $this->getFile());
        if (!$type instanceof Type) {
            return \false;
        }
        return $type->isArray()->yes();
    }
    private function isInitializedProperty(PhpPropertyReflection $phpPropertyReflection, string $propertyName): bool
    {
        $propertyNode = $this->astResolver->resolvePropertyFromPropertyReflection($phpPropertyReflection);
        // promoted property is assigned on construct
        if ($propertyNode instanceof Param) {
            return \true;
        }
        if (!$propertyNode instanceof Property) {
            return \false;
        }
        if ($propertyNode->props[0]->default !== null) {
            return \true;
        }
        $classLike = $this->astResolver->resolveClassFromClassReflection($phpPropertyReflection->getDeclaringClass());
        if ($classLike === null) {
            return \false;
        }
        return $this->constructorAssignDetector->isPropertyAssigned($classLike, $propertyName);
    }
}

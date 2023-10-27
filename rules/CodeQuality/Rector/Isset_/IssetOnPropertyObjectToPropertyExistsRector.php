<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Isset_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\IssetOnPropertyObjectToPropertyExistsRectorTest
 *
 * @changelog https://3v4l.org/TI8XL Change isset on property object to property_exists() with not null check
 */
final class IssetOnPropertyObjectToPropertyExistsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver, ValueResolver $valueResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change isset on property object to property_exists() and not null check', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private $x;

    public function run(): void
    {
        isset($this->x);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private $x;

    public function run(): void
    {
        property_exists($this, 'x') && $this->x !== null;
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
        return [Isset_::class, BooleanNot::class];
    }
    /**
     * @param Isset_|BooleanNot $node
     */
    public function refactor(Node $node) : ?Node
    {
        $isNegated = \false;
        if ($node instanceof BooleanNot) {
            if ($node->expr instanceof Isset_) {
                $isNegated = \true;
                $isset = $node->expr;
            } else {
                return null;
            }
        } else {
            $isset = $node;
        }
        $newNodes = [];
        foreach ($isset->vars as $issetExpr) {
            if (!$issetExpr instanceof PropertyFetch) {
                continue;
            }
            // has property PHP 7.4 type?
            if ($this->shouldSkipForPropertyTypeDeclaration($issetExpr)) {
                continue;
            }
            // Ignore dynamically accessed properties ($o->$p)
            $propertyFetchName = $this->getName($issetExpr->name);
            if (!\is_string($propertyFetchName)) {
                continue;
            }
            $classReflection = $this->matchPropertyTypeClassReflection($issetExpr);
            if (!$classReflection instanceof ClassReflection) {
                continue;
            }
            if (!$classReflection->hasProperty($propertyFetchName) || $classReflection->isBuiltin()) {
                $newNodes[] = $this->replaceToPropertyExistsWithNullCheck($issetExpr->var, $propertyFetchName, $issetExpr, $isNegated);
            } elseif ($isNegated) {
                $newNodes[] = $this->createIdenticalToNull($issetExpr);
            } else {
                $newNodes[] = $this->createNotIdenticalToNull($issetExpr);
            }
        }
        return $this->nodeFactory->createReturnBooleanAnd($newNodes);
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BinaryOp\BooleanOr
     */
    private function replaceToPropertyExistsWithNullCheck(Expr $expr, string $property, PropertyFetch $propertyFetch, bool $isNegated)
    {
        $args = [new Arg($expr), new Arg(new String_($property))];
        $propertyExistsFuncCall = $this->nodeFactory->createFuncCall('property_exists', $args);
        if ($isNegated) {
            $booleanNot = new BooleanNot($propertyExistsFuncCall);
            return new BooleanOr($booleanNot, $this->createIdenticalToNull($propertyFetch));
        }
        return new BooleanAnd($propertyExistsFuncCall, $this->createNotIdenticalToNull($propertyFetch));
    }
    private function createNotIdenticalToNull(PropertyFetch $propertyFetch) : NotIdentical
    {
        return new NotIdentical($propertyFetch, $this->nodeFactory->createNull());
    }
    private function shouldSkipForPropertyTypeDeclaration(PropertyFetch $propertyFetch) : bool
    {
        if (!$propertyFetch->name instanceof Identifier) {
            return \true;
        }
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($propertyFetch);
        if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
            return \false;
        }
        $propertyType = $phpPropertyReflection->getNativeType();
        if ($propertyType instanceof MixedType) {
            return \false;
        }
        if (!TypeCombinator::containsNull($propertyType)) {
            return \true;
        }
        $nativeReflectionProperty = $phpPropertyReflection->getNativeReflection();
        if (!$nativeReflectionProperty->hasDefaultValue()) {
            return \true;
        }
        $defaultValueExpr = $nativeReflectionProperty->getDefaultValueExpression();
        return !$this->valueResolver->isNull($defaultValueExpr);
    }
    private function createIdenticalToNull(PropertyFetch $propertyFetch) : Identical
    {
        return new Identical($propertyFetch, $this->nodeFactory->createNull());
    }
    private function matchPropertyTypeClassReflection(PropertyFetch $propertyFetch) : ?ClassReflection
    {
        $propertyFetchVarType = $this->getType($propertyFetch->var);
        if (!$propertyFetchVarType instanceof TypeWithClassName) {
            return null;
        }
        if ($propertyFetchVarType->getClassName() === 'stdClass') {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($propertyFetchVarType->getClassName())) {
            return null;
        }
        return $this->reflectionProvider->getClass($propertyFetchVarType->getClassName());
    }
}

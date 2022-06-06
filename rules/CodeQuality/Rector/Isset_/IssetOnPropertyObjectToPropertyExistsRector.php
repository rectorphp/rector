<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Isset_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\IssetOnPropertyObjectToPropertyExistsRectorTest
 *
 * @changelog https://3v4l.org/TI8XL Change isset on property object to property_exists() with not null check
 */
final class IssetOnPropertyObjectToPropertyExistsRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change isset on property object to property_exists() and not null check', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\Isset_::class];
    }
    /**
     * @param Isset_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $newNodes = [];
        foreach ($node->vars as $issetVar) {
            if (!$issetVar instanceof \PhpParser\Node\Expr\PropertyFetch) {
                continue;
            }
            // Ignore dynamically accessed properties ($o->$p)
            if ($issetVar->name instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            // has property PHP 7.4 type?
            if ($this->hasPropertyTypeDeclaration($issetVar)) {
                continue;
            }
            $propertyFetchName = $this->getName($issetVar->name);
            if ($propertyFetchName === null) {
                continue;
            }
            $propertyFetchVarType = $this->getType($issetVar->var);
            if ($propertyFetchVarType instanceof \PHPStan\Type\TypeWithClassName) {
                if (!$this->reflectionProvider->hasClass($propertyFetchVarType->getClassName())) {
                    continue;
                }
                $classReflection = $this->reflectionProvider->getClass($propertyFetchVarType->getClassName());
                if (!$classReflection->hasProperty($propertyFetchName) || $classReflection->isBuiltin()) {
                    $newNodes[] = $this->replaceToPropertyExistsWithNullCheck($issetVar->var, $propertyFetchName, $issetVar);
                } else {
                    $newNodes[] = $this->createNotIdenticalToNull($issetVar);
                }
            } else {
                $newNodes[] = $this->replaceToPropertyExistsWithNullCheck($issetVar->var, $propertyFetchName, $issetVar);
            }
        }
        return $this->nodeFactory->createReturnBooleanAnd($newNodes);
    }
    private function replaceToPropertyExistsWithNullCheck(\PhpParser\Node\Expr $expr, string $property, \PhpParser\Node\Expr\PropertyFetch $propertyFetch) : \PhpParser\Node\Expr\BinaryOp\BooleanAnd
    {
        $args = [new \PhpParser\Node\Arg($expr), new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_($property))];
        $propertyExistsFuncCall = $this->nodeFactory->createFuncCall('property_exists', $args);
        return new \PhpParser\Node\Expr\BinaryOp\BooleanAnd($propertyExistsFuncCall, $this->createNotIdenticalToNull($propertyFetch));
    }
    private function createNotIdenticalToNull(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : \PhpParser\Node\Expr\BinaryOp\NotIdentical
    {
        return new \PhpParser\Node\Expr\BinaryOp\NotIdentical($propertyFetch, $this->nodeFactory->createNull());
    }
    private function hasPropertyTypeDeclaration(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : bool
    {
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($propertyFetch);
        if (!$phpPropertyReflection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection) {
            return \false;
        }
        return !$phpPropertyReflection->getNativeType() instanceof \PHPStan\Type\MixedType;
    }
}

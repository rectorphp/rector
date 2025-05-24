<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpParameterReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\MethodCall\SetArrayToNewCollectionRector\SetArrayToNewCollectionRectorTest
 */
final class SetArrayToNewCollectionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change array to new ArrayCollection() on collection typed property', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;

final class SomeClass
{
    /**
     * @var ArrayCollection<int, string>
     */
    public $items;

    public function someMethod()
    {
        $this->items = [];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function someMethod()
    {
        $this->items = new ArrayCollection([]);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [MethodCall::class, New_::class];
    }
    /**
     * @param MethodCall|New_ $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_|null
     */
    public function refactor(Node $node)
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getArgs() as $position => $arg) {
            $soleArgType = $this->getType($arg->value);
            if ($soleArgType instanceof ObjectType) {
                continue;
            }
            if (!$this->isCallWithCollectionParam($node, $position)) {
                continue;
            }
            $oldArgValue = $arg->value;
            // wrap argument with a collection instance
            $defaultExpr = $this->isName($oldArgValue, 'null') ? new Array_() : $oldArgValue;
            $arg->value = new New_(new FullyQualified(DoctrineClass::ARRAY_COLLECTION), [new Arg($defaultExpr)]);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_ $methodCallOrNew
     */
    private function isCallWithCollectionParam($methodCallOrNew, int $position) : bool
    {
        if ($methodCallOrNew instanceof MethodCall) {
            // does setter method require a collection?
            $callerType = $this->getType($methodCallOrNew->var);
            $methodName = $this->getName($methodCallOrNew->name);
        } else {
            $callerType = $this->getType($methodCallOrNew->class);
            $methodName = MethodName::CONSTRUCT;
        }
        $callerType = TypeCombinator::removeNull($callerType);
        if (!$callerType instanceof ObjectType) {
            return \false;
        }
        if (!$this->reflectionProvider->hasClass($callerType->getClassName())) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($callerType->getClassName());
        if ($methodName === null) {
            return \false;
        }
        $scope = ScopeFetcher::fetch($methodCallOrNew);
        if (!$classReflection->hasMethod($methodName)) {
            return \false;
        }
        $extendedMethodReflection = $classReflection->getMethod($methodName, $scope);
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        $activeParameterReflection = $extendedParametersAcceptor->getParameters()[$position] ?? null;
        if (!$activeParameterReflection instanceof PhpParameterReflection) {
            return \false;
        }
        if ($activeParameterReflection->getType() instanceof ObjectType) {
            /** @var ObjectType $paramObjectType */
            $paramObjectType = $activeParameterReflection->getType();
            return $paramObjectType->isInstanceOf(DoctrineClass::COLLECTION)->yes();
        }
        return \false;
    }
}

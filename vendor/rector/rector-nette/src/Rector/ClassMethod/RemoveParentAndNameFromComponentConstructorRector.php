<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Nette\NodeAnalyzer\StaticCallAnalyzer;
use Rector\Nette\NodeFinder\ParamFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/nette/component-model/commit/1fb769f4602cf82694941530bac1111b3c5cd11b
 * This only applied to child of \Nette\Application\UI\Control, not Forms! Forms still need to be attached to their parents
 *
 * @see \Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\RemoveParentAndNameFromComponentConstructorRectorTest
 */
final class RemoveParentAndNameFromComponentConstructorRector extends AbstractRector
{
    /**
     * @var string
     */
    private const PARENT = 'parent';
    /**
     * @var string
     */
    private const NAME = 'name';
    /**
     * @readonly
     * @var \PHPStan\Type\ObjectType
     */
    private $controlObjectType;
    /**
     * @readonly
     * @var \Rector\Nette\NodeFinder\ParamFinder
     */
    private $paramFinder;
    /**
     * @readonly
     * @var \Rector\Nette\NodeAnalyzer\StaticCallAnalyzer
     */
    private $staticCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ParamFinder $paramFinder, StaticCallAnalyzer $staticCallAnalyzer, ReflectionResolver $reflectionResolver)
    {
        $this->paramFinder = $paramFinder;
        $this->staticCallAnalyzer = $staticCallAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->controlObjectType = new ObjectType('Nette\\Application\\UI\\Control');
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove $parent and $name in control constructor', [new CodeSample(<<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function __construct(IContainer $parent = null, $name = null, int $value)
    {
        parent::__construct($parent, $name);
        $this->value = $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function __construct(int $value)
    {
        $this->value = $value;
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
        return [ClassMethod::class, StaticCall::class, New_::class];
    }
    /**
     * @param ClassMethod|StaticCall|New_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }
        if ($node instanceof StaticCall) {
            return $this->refactorStaticCall($node);
        }
        if ($this->isObjectType($node->class, new ObjectType('Nette\\Application\\UI\\Control'))) {
            $this->refactorNew($node);
            return $node;
        }
        return null;
    }
    private function refactorClassMethod(ClassMethod $classMethod) : ?ClassMethod
    {
        if (!$this->isInsideNetteComponentClass($classMethod)) {
            return null;
        }
        if (!$this->isName($classMethod, MethodName::CONSTRUCT)) {
            return null;
        }
        $this->removeClassMethodParams($classMethod);
        return $classMethod;
    }
    private function refactorStaticCall(StaticCall $staticCall) : ?StaticCall
    {
        if (!$this->isInsideNetteComponentClass($staticCall)) {
            return null;
        }
        if (!$this->staticCallAnalyzer->isParentCallNamed($staticCall, MethodName::CONSTRUCT)) {
            return null;
        }
        foreach ($staticCall->args as $position => $staticCallArg) {
            if (!$staticCallArg->value instanceof Variable) {
                continue;
            }
            /** @var Variable $variable */
            $variable = $staticCallArg->value;
            if (!$this->isNames($variable, [self::NAME, self::PARENT])) {
                continue;
            }
            unset($staticCall->args[$position]);
        }
        if ($staticCall->args === []) {
            $this->removeNode($staticCall);
            return null;
        }
        return $staticCall;
    }
    private function refactorNew(New_ $new) : void
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($new);
        if (!$methodReflection instanceof MethodReflection) {
            return;
        }
        $parameterNames = [];
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            /** @var ParameterReflection $parameterReflection */
            $parameterNames[] = $parameterReflection->getName();
        }
        foreach (\array_keys($new->args) as $position) {
            // is on position of $parent or $name?
            if (!isset($parameterNames[$position])) {
                continue;
            }
            $parameterName = $parameterNames[$position];
            if (!\in_array($parameterName, [self::PARENT, self::NAME], \true)) {
                continue;
            }
            unset($new->args[$position]);
        }
    }
    private function isInsideNetteComponentClass(Node $node) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        // presenter is not a component
        if ($classReflection->isSubclassOf('Nette\\Application\\UI\\Presenter')) {
            return \false;
        }
        return $classReflection->isSubclassOf($this->controlObjectType->getClassName());
    }
    private function removeClassMethodParams(ClassMethod $classMethod) : void
    {
        foreach ($classMethod->params as $param) {
            if ($this->paramFinder->isInAssign((array) $classMethod->stmts, $param)) {
                continue;
            }
            if ($this->isObjectType($param, new ObjectType('Nette\\ComponentModel\\IContainer'))) {
                $this->removeNode($param);
                continue;
            }
            if ($this->isName($param, self::NAME)) {
                $this->removeNode($param);
            }
        }
    }
}

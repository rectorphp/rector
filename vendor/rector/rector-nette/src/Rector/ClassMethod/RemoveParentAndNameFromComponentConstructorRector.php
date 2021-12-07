<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
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
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/nette/component-model/commit/1fb769f4602cf82694941530bac1111b3c5cd11b
 * This only applied to child of \Nette\Application\UI\Control, not Forms! Forms still need to be attached to their parents
 *
 * @see \Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\RemoveParentAndNameFromComponentConstructorRectorTest
 */
final class RemoveParentAndNameFromComponentConstructorRector extends \Rector\Core\Rector\AbstractRector
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
     * @var \PHPStan\Type\ObjectType
     */
    private $controlObjectType;
    /**
     * @var \Rector\Nette\NodeFinder\ParamFinder
     */
    private $paramFinder;
    /**
     * @var \Rector\Nette\NodeAnalyzer\StaticCallAnalyzer
     */
    private $staticCallAnalyzer;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Nette\NodeFinder\ParamFinder $paramFinder, \Rector\Nette\NodeAnalyzer\StaticCallAnalyzer $staticCallAnalyzer, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->paramFinder = $paramFinder;
        $this->staticCallAnalyzer = $staticCallAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->controlObjectType = new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Control');
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove $parent and $name in control constructor', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\New_::class];
    }
    /**
     * @param ClassMethod|StaticCall|New_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return $this->refactorClassMethod($node);
        }
        if ($node instanceof \PhpParser\Node\Expr\StaticCall) {
            return $this->refactorStaticCall($node);
        }
        if ($this->isObjectType($node->class, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Control'))) {
            $this->refactorNew($node);
            return $node;
        }
        return null;
    }
    private function refactorClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if (!$this->isInsideNetteComponentClass($classMethod)) {
            return null;
        }
        if (!$this->isName($classMethod, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return null;
        }
        $this->removeClassMethodParams($classMethod);
        return $classMethod;
    }
    private function refactorStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : ?\PhpParser\Node\Expr\StaticCall
    {
        if (!$this->isInsideNetteComponentClass($staticCall)) {
            return null;
        }
        if (!$this->staticCallAnalyzer->isParentCallNamed($staticCall, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return null;
        }
        foreach ($staticCall->args as $staticCallArg) {
            if (!$staticCallArg->value instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            /** @var Variable $variable */
            $variable = $staticCallArg->value;
            if (!$this->isNames($variable, [self::NAME, self::PARENT])) {
                continue;
            }
            $this->removeNode($staticCallArg);
        }
        if ($this->shouldRemoveEmptyCall($staticCall)) {
            $this->removeNode($staticCall);
            return null;
        }
        return $staticCall;
    }
    private function refactorNew(\PhpParser\Node\Expr\New_ $new) : void
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($new);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return;
        }
        $parameterNames = [];
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            /** @var ParameterReflection $parameterReflection */
            $parameterNames[] = $parameterReflection->getName();
        }
        foreach ($new->args as $position => $arg) {
            // is on position of $parent or $name?
            if (!isset($parameterNames[$position])) {
                continue;
            }
            $parameterName = $parameterNames[$position];
            if (!\in_array($parameterName, [self::PARENT, self::NAME], \true)) {
                continue;
            }
            $this->removeNode($arg);
        }
    }
    private function isInsideNetteComponentClass(\PhpParser\Node $node) : bool
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        // presenter is not a component
        if ($classReflection->isSubclassOf('Nette\\Application\\UI\\Presenter')) {
            return \false;
        }
        return $classReflection->isSubclassOf($this->controlObjectType->getClassName());
    }
    private function removeClassMethodParams(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        foreach ($classMethod->params as $param) {
            if ($this->paramFinder->isInAssign((array) $classMethod->stmts, $param)) {
                continue;
            }
            if ($this->isObjectType($param, new \PHPStan\Type\ObjectType('Nette\\ComponentModel\\IContainer'))) {
                $this->removeNode($param);
                continue;
            }
            if ($this->isName($param, self::NAME)) {
                $this->removeNode($param);
            }
        }
    }
    private function shouldRemoveEmptyCall(\PhpParser\Node\Expr\StaticCall $staticCall) : bool
    {
        foreach ($staticCall->args as $arg) {
            if ($this->nodesToRemoveCollector->isNodeRemoved($arg)) {
                continue;
            }
            return \false;
        }
        return \true;
    }
}

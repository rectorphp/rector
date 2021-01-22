<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Nette\NodeAnalyzer\StaticCallAnalyzer;
use Rector\Nette\NodeFinder\ParamFinder;
use Rector\NodeCollector\Reflection\MethodReflectionProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    private const COMPONENT_CONTAINER_CLASS = 'Nette\ComponentModel\IContainer';

    /**
     * @var string
     */
    private const PARENT = 'parent';

    /**
     * @var string
     */
    private const NAME = 'name';

    /**
     * Package "nette/application" is required for DEV, might not exist for PROD.
     * So access the class throgh the string
     *
     * @var string
     */
    private const CONTROL_CLASS = 'Nette\Application\UI\Control';

    /**
     * @var string
     */
    private const PRESENTER_CLASS = 'Nette\Application\UI\Presenter';

    /**
     * @var StaticCallAnalyzer
     */
    private $staticCallAnalyzer;

    /**
     * @var MethodReflectionProvider
     */
    private $methodReflectionProvider;

    /**
     * @var ParamFinder
     */
    private $paramFinder;

    public function __construct(
        ParamFinder $paramFinder,
        StaticCallAnalyzer $staticCallAnalyzer,
        MethodReflectionProvider $methodReflectionProvider
    ) {
        $this->staticCallAnalyzer = $staticCallAnalyzer;
        $this->methodReflectionProvider = $methodReflectionProvider;
        $this->paramFinder = $paramFinder;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove $parent and $name in control constructor',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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

                    ,
                    <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function __construct(int $value)
    {
        $this->value = $value;
    }
}
CODE_SAMPLE

                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, StaticCall::class, New_::class];
    }

    /**
     * @param ClassMethod|StaticCall|New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }

        if ($node instanceof StaticCall) {
            return $this->refactorStaticCall($node);
        }

        if ($this->isObjectType($node->class, self::CONTROL_CLASS)) {
            return $this->refactorNew($node);
        }

        return null;
    }

    private function refactorClassMethod(ClassMethod $classMethod): ?ClassMethod
    {
        if (! $this->isInsideNetteComponentClass($classMethod)) {
            return null;
        }

        if (! $this->isName($classMethod, MethodName::CONSTRUCT)) {
            return null;
        }

        return $this->removeClassMethodParams($classMethod);
    }

    private function refactorStaticCall(StaticCall $staticCall): ?StaticCall
    {
        if (! $this->isInsideNetteComponentClass($staticCall)) {
            return null;
        }

        if (! $this->staticCallAnalyzer->isParentCallNamed($staticCall, MethodName::CONSTRUCT)) {
            return null;
        }

        foreach ($staticCall->args as $staticCallArg) {
            if (! $staticCallArg->value instanceof Variable) {
                continue;
            }

            /** @var Variable $variable */
            $variable = $staticCallArg->value;
            if (! $this->isNames($variable, [self::NAME, self::PARENT])) {
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

    private function refactorNew(New_ $new): New_
    {
        $parameterNames = $this->methodReflectionProvider->provideParameterNamesByNew($new);

        foreach ($new->args as $position => $arg) {
            // is on position of $parent or $name?
            if (! isset($parameterNames[$position])) {
                continue;
            }

            $parameterName = $parameterNames[$position];
            if (! in_array($parameterName, [self::PARENT, self::NAME], true)) {
                continue;
            }

            $this->removeNode($arg);
        }

        return $new;
    }

    private function isInsideNetteComponentClass(Node $node): bool
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        if ($this->isObjectType($classLike, self::PRESENTER_CLASS)) {
            return false;
        }

        return $this->isObjectType($classLike, self::CONTROL_CLASS);
    }

    private function removeClassMethodParams(ClassMethod $classMethod): ClassMethod
    {
        foreach ($classMethod->params as $param) {
            if ($this->paramFinder->isInAssign((array) $classMethod->stmts, $param)) {
                continue;
            }

            if ($this->isObjectType($param, self::COMPONENT_CONTAINER_CLASS)) {
                $this->removeNode($param);
                continue;
            }

            if ($this->isName($param, self::NAME)) {
                $this->removeNode($param);
            }
        }

        return $classMethod;
    }

    private function shouldRemoveEmptyCall(StaticCall $staticCall): bool
    {
        foreach ($staticCall->args as $arg) {
            if ($this->isNodeRemoved($arg)) {
                continue;
            }

            return false;
        }

        return true;
    }
}

<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\ClassMethod;

use Nette\Application\UI\Control;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\MethodName;
use Rector\Nette\NodeAnalyzer\StaticCallAnalyzer;

/**
 * @see https://github.com/nette/component-model/commit/1fb769f4602cf82694941530bac1111b3c5cd11b
 *
 * @see \Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\RemoveParentAndNameFromComponentConstructorRectorTest
 */
final class RemoveParentAndNameFromComponentConstructorRector extends AbstractRector
{
    /**
     * @var StaticCallAnalyzer
     */
    private $staticCallAnalyzer;

    public function __construct(StaticCallAnalyzer $staticCallAnalyzer)
    {
        $this->staticCallAnalyzer = $staticCallAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove $parent and $name in control constructor', [
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

        return null;
    }

    private function refactorClassMethod(ClassMethod $classMethod): ?ClassMethod
    {
        if (! $this->isInObjectType($classMethod, Control::class)) {
            return null;
        }

        if (! $this->isName($classMethod, MethodName::CONSTRUCT)) {
            return null;
        }

        $hasClassMethodChanged = false;
        foreach ($classMethod->params as $param) {
            if ($this->isName($param, 'parent') && $param->type !== null && $this->isName(
                $param->type,
                'Nette\ComponentModel\IContainer'
            )) {
                $this->removeNode($param);
                $hasClassMethodChanged = true;
            }

            if ($this->isName($param, 'name')) {
                $this->removeNode($param);
                $hasClassMethodChanged = true;
            }
        }

        if ($hasClassMethodChanged === false) {
            return null;
        }

        return $classMethod;
    }

    private function refactorStaticCall(StaticCall $staticCall): ?StaticCall
    {
        if (! $this->staticCallAnalyzer->isParentCallNamed($staticCall, MethodName::CONSTRUCT)) {
            return null;
        }

        $hasStaticCallChanged = false;

        /** @var Arg $staticCallArg */
        foreach ((array) $staticCall->args as $staticCallArg) {
            if (! $staticCallArg->value instanceof Variable) {
                continue;
            }

            /** @var Variable $variable */
            $variable = $staticCallArg->value;
            if (! $this->isNames($variable, ['name', 'parent'])) {
                continue;
            }

            $this->removeNode($staticCallArg);
            $hasStaticCallChanged = true;
        }

        if ($hasStaticCallChanged === false) {
            return null;
        }

        if ($this->shouldRemoveEmptyCall($staticCall)) {
            $this->removeNode($staticCall);
            return null;
        }

        return $staticCall;
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

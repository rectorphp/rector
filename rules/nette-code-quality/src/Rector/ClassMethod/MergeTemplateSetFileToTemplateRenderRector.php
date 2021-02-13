<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\NetteCodeQuality\Tests\Rector\ClassMethod\MergeTemplateSetFileToTemplateRenderRector\MergeTemplateSetFileToTemplateRenderRectorTest
 */
final class MergeTemplateSetFileToTemplateRenderRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change $this->template->setFile() $this->template->render()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

final class SomeControl extends Control
{
    public function render()
    {
        $this->template->setFile(__DIR__ . '/someFile.latte');
        $this->template->render();
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

final class SomeControl extends Control
{
    public function render()
    {
        $this->template->render(__DIR__ . '/someFile.latte');
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstanceOf((array) $node->stmts, MethodCall::class);

        $setFileMethodCall = $this->resolveSingleSetFileMethodCall($methodCalls);
        if (! $setFileMethodCall instanceof MethodCall) {
            return null;
        }

        foreach ($methodCalls as $methodCall) {
            if (! $this->isName($methodCall->name, 'render')) {
                continue;
            }

            if (isset($methodCall->args[0])) {
                continue;
            }

            $this->removeNode($setFileMethodCall);
            $methodCall->args[0] = new Arg($setFileMethodCall->args[0]->value);
            return $node;
        }

        return null;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            return true;
        }

        if ($this->isObjectType($classMethod, 'Nette\Application\UI\Presenter')) {
            return true;
        }

        return ! $this->isObjectType($classMethod, 'Nette\Application\UI\Control');
    }

    /**
     * @param MethodCall[] $methodCalls
     */
    private function resolveSingleSetFileMethodCall(array $methodCalls): ?MethodCall
    {
        $singleSetFileMethodCall = null;
        foreach ($methodCalls as $methodCall) {
            if (! $this->isName($methodCall->name, 'setFile')) {
                continue;
            }

            if ($singleSetFileMethodCall instanceof MethodCall) {
                return null;
            }

            $singleSetFileMethodCall = $methodCall;
        }

        return $singleSetFileMethodCall;
    }
}

<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see https://doc.nette.org/en/3.0/components#toc-advanced-use-of-components
 *
 * @see \Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ArrayDimFetchControlToGetComponentMethodCallRector\ArrayDimFetchControlToGetComponentMethodCallRectorTest
 */
final class ArrayDimFetchControlToGetComponentMethodCallRector extends AbstractArrayDimFetchToAnnotatedControlVariableRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change array dim $this["someComponent"] to more explicit $this->getComponent("someComponent")',
            [
                new CodeSample(
                    <<<'PHP'
use Nette\Application\UI\Presenter;

class SomePresenter extends Presenter
{
    public function someAction()
    {
        $form = $this['someForm'];
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}
PHP
,
                    <<<'PHP'
use Nette\Application\UI\Presenter;

class SomePresenter extends Presenter
{
    public function someAction()
    {
        $form = $this->getComponent('someForm');
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}
PHP
            ),
            ]
        );
    }

    /**
     * @param ArrayDimFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $controlName = $this->controlDimFetchAnalyzer->matchNameOnControlVariable($node);

        return new MethodCall($node->var, 'getComponent', $this->createArgs([$controlName]));
    }

    private function shouldSkip(ArrayDimFetch $arrayDimFetch): bool
    {
        $parent = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Assign && $parent->var === $arrayDimFetch) {
            return true;
        }

        return $parent instanceof MethodCall;
    }
}

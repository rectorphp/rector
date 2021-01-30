<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see https://github.com/nette/component-model/blob/c1fb11729423379768a71dd865ae373a3b12fa43/src/ComponentModel/Container.php#L110
 *
 * @see \Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector\ArrayAccessGetControlToGetComponentMethodCallRectorTest
 */
final class ArrayAccessGetControlToGetComponentMethodCallRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change magic arrays access get, to explicit $this->getComponent(...) method',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = $this['whatever'];
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = $this->getComponent('whatever');
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
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isFetchOfControlFromPresenterDimFetch($node)) {
            return null;
        }

        /** @var ArrayDimFetch $arrayDimFetch */
        $arrayDimFetch = $node->expr;

        $args = $this->nodeFactory->createArgs([$arrayDimFetch->dim]);
        $node->expr = new MethodCall($arrayDimFetch->var, 'getComponent', $args);

        return $node;
    }

    private function isFetchOfControlFromPresenterDimFetch(Assign $assign): bool
    {
        if (! $assign->expr instanceof ArrayDimFetch) {
            return false;
        }

        return $this->isObjectType($assign->expr, 'Nette\Application\UI\Presenter');
    }
}

<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector\ArrayAccessSetControlToAddComponentMethodCallRectorTest
 *
 * @see https://github.com/nette/component-model/blob/c1fb11729423379768a71dd865ae373a3b12fa43/src/ComponentModel/Container.php#L39
 */
final class ArrayAccessSetControlToAddComponentMethodCallRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change magic arrays access set, to explicit $this->setComponent(...) method', [new CodeSample(<<<'CODE_SAMPLE'
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = new Control();
        $this['whatever'] = $someControl;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = new Control();
        $this->addComponent($someControl, 'whatever');
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
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isAssignOfControlToPresenterDimFetch($node)) {
            return null;
        }
        /** @var ArrayDimFetch $arrayDimFetch */
        $arrayDimFetch = $node->var;
        $arguments = [$node->expr, $arrayDimFetch->dim];
        $arg = $this->nodeFactory->createArgs($arguments);
        return new MethodCall($arrayDimFetch->var, 'addComponent', $arg);
    }
    private function isAssignOfControlToPresenterDimFetch(Assign $assign) : bool
    {
        if (!$assign->var instanceof ArrayDimFetch) {
            return \false;
        }
        if (!$this->isObjectType($assign->expr, new ObjectType('Nette\\Application\\UI\\Control'))) {
            return \false;
        }
        $arrayDimFetch = $assign->var;
        if (!$arrayDimFetch->var instanceof Variable) {
            return \false;
        }
        return $this->isObjectType($arrayDimFetch->var, new ObjectType('Nette\\Application\\UI\\Presenter'));
    }
}

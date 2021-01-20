<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Nette\Tests\Rector\MethodCall\AddNextrasDatePickerToDateControlRector\AddNextrasDatePickerToDateControlRectorTest
 */
final class AddNextrasDatePickerToDateControlRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Nextras/Form upgrade of addDatePicker method call to DateControl assign',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Nette\Application\UI\Form;

class SomeClass
{
    public function run()
    {
        $form = new Form();
        $form->addDatePicker('key', 'Label');
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
use Nette\Application\UI\Form;

class SomeClass
{
    public function run()
    {
        $form = new Form();
        $form['key'] = new \Nextras\FormComponents\Controls\DateControl('Label');
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        // 1. chain call
        if ($node->var instanceof MethodCall) {
            if (! $this->isOnClassMethodCall($node->var, 'Nette\Application\UI\Form', 'addDatePicker')) {
                return null;
            }

            $assign = $this->createAssign($node->var);
            if (! $assign instanceof Node) {
                return null;
            }

            $controlName = $this->resolveControlName($node->var);
            $node->var = new Variable($controlName);

            // this fixes printing indent
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $this->addNodeBeforeNode($assign, $node);

            return $node;
        }

        // 2. assign call
        if (! $this->isOnClassMethodCall($node, 'Nette\Application\UI\Form', 'addDatePicker')) {
            return null;
        }

        return $this->createAssign($node);
    }

    private function createAssign(MethodCall $methodCall): ?Node
    {
        $key = $methodCall->args[0]->value;
        if (! $key instanceof String_) {
            return null;
        }

        $new = $this->createDateTimeControlNew($methodCall);

        $parent = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Assign) {
            return $new;
        }

        $arrayDimFetch = new ArrayDimFetch($methodCall->var, $key);
        $new = $this->createDateTimeControlNew($methodCall);

        $formAssign = new Assign($arrayDimFetch, $new);

        if ($parent !== null) {
            $methodCalls = $this->betterNodeFinder->findInstanceOf($parent, MethodCall::class);

            if (count($methodCalls) > 1) {
                $controlName = $this->resolveControlName($methodCall);
                return new Assign(new Variable($controlName), $formAssign);
            }
        }

        return $formAssign;
    }

    private function resolveControlName(MethodCall $methodCall): string
    {
        $controlName = $methodCall->args[0]->value;
        if (! $controlName instanceof String_) {
            throw new ShouldNotHappenException();
        }

        return $controlName->value . 'DateControl';
    }

    private function createDateTimeControlNew(MethodCall $methodCall): New_
    {
        $fullyQualified = new FullyQualified('Nextras\FormComponents\Controls\DateControl');
        $new = new New_($fullyQualified);

        if (isset($methodCall->args[1])) {
            $new->args[] = $methodCall->args[1];
        }

        return $new;
    }
}

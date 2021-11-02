<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\MethodCall\AddNextrasDatePickerToDateControlRector\AddNextrasDatePickerToDateControlRectorTest
 */
final class AddNextrasDatePickerToDateControlRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Nextras/Form upgrade of addDatePicker method call to DateControl assign', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        // 1. chain call
        if ($node->var instanceof \PhpParser\Node\Expr\MethodCall) {
            if (!$this->isObjectType($node->var->var, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Form'))) {
                return null;
            }
            if (!$this->isName($node->var->name, 'addDatePicker')) {
                return null;
            }
            $assign = $this->createAssign($node->var);
            if (!$assign instanceof \PhpParser\Node) {
                return null;
            }
            $controlName = $this->resolveControlName($node->var);
            $node->var = new \PhpParser\Node\Expr\Variable($controlName);
            // this fixes printing indent
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
            $this->nodesToAddCollector->addNodeBeforeNode($assign, $node);
            return $node;
        }
        // 2. assign call
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Form'))) {
            return null;
        }
        if (!$this->isName($node->name, 'addDatePicker')) {
            return null;
        }
        return $this->createAssign($node);
    }
    private function createAssign(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        $key = $methodCall->args[0]->value;
        if (!$key instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        $new = $this->createDateTimeControlNew($methodCall);
        $parent = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Expr\Assign) {
            return $new;
        }
        $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch($methodCall->var, $key);
        $formAssign = new \PhpParser\Node\Expr\Assign($arrayDimFetch, $new);
        if ($parent instanceof \PhpParser\Node) {
            $methodCalls = $this->betterNodeFinder->findInstanceOf($parent, \PhpParser\Node\Expr\MethodCall::class);
            if (\count($methodCalls) > 1) {
                $controlName = $this->resolveControlName($methodCall);
                return new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable($controlName), $formAssign);
            }
        }
        return $formAssign;
    }
    private function resolveControlName(\PhpParser\Node\Expr\MethodCall $methodCall) : string
    {
        $controlName = $methodCall->args[0]->value;
        if (!$controlName instanceof \PhpParser\Node\Scalar\String_) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $controlName->value . 'DateControl';
    }
    private function createDateTimeControlNew(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Expr\New_
    {
        $fullyQualified = new \PhpParser\Node\Name\FullyQualified('Nextras\\FormComponents\\Controls\\DateControl');
        $new = new \PhpParser\Node\Expr\New_($fullyQualified);
        if (isset($methodCall->args[1])) {
            $new->args[] = $methodCall->args[1];
        }
        return $new;
    }
}

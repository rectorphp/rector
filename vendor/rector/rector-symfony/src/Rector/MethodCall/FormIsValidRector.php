<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\MethodCallManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\FormIsValidRector\FormIsValidRectorTest
 */
final class FormIsValidRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\MethodCallManipulator
     */
    private $methodCallManipulator;
    public function __construct(\Rector\Core\NodeManipulator\MethodCallManipulator $methodCallManipulator)
    {
        $this->methodCallManipulator = $methodCallManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Adds `$form->isSubmitted()` validation to all `$form->isValid()` calls in Form in Symfony', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
if ($form->isValid()) {
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if ($form->isSubmitted() && $form->isValid()) {
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
        if ($this->shouldSkipMethodCall($node)) {
            return null;
        }
        /** @var Variable $variable */
        $variable = $node->var;
        if ($this->isIsSubmittedByAlreadyCalledOnVariable($variable)) {
            return null;
        }
        /** @var string $variableName */
        $variableName = $this->getName($node->var);
        return new \PhpParser\Node\Expr\BinaryOp\BooleanAnd($this->nodeFactory->createMethodCall($variableName, 'isSubmitted'), $this->nodeFactory->createMethodCall($variableName, 'isValid'));
    }
    private function shouldSkipMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        $originalNode = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE);
        // skip just added calls
        if (!$originalNode instanceof \PhpParser\Node) {
            return \true;
        }
        if (!$this->isObjectType($methodCall->var, new \PHPStan\Type\ObjectType('Symfony\\Component\\Form\\Form'))) {
            return \true;
        }
        if (!$this->isName($methodCall->name, 'isValid')) {
            return \true;
        }
        $previousNode = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
        if ($previousNode !== null) {
            return \true;
        }
        $variableName = $this->getName($methodCall->var);
        return $variableName === null;
    }
    private function isIsSubmittedByAlreadyCalledOnVariable(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        $previousMethodCallNamesOnVariable = $this->methodCallManipulator->findMethodCallNamesOnVariable($variable);
        // already checked by isSubmitted()
        return \in_array('isSubmitted', $previousMethodCallNamesOnVariable, \true);
    }
}

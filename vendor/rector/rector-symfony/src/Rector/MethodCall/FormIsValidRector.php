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
final class FormIsValidRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\MethodCallManipulator
     */
    private $methodCallManipulator;
    public function __construct(MethodCallManipulator $methodCallManipulator)
    {
        $this->methodCallManipulator = $methodCallManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Adds `$form->isSubmitted()` validation to all `$form->isValid()` calls in Form in Symfony', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
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
        return new BooleanAnd($this->nodeFactory->createMethodCall($variableName, 'isSubmitted'), $this->nodeFactory->createMethodCall($variableName, 'isValid'));
    }
    private function shouldSkipMethodCall(MethodCall $methodCall) : bool
    {
        $originalNode = $methodCall->getAttribute(AttributeKey::ORIGINAL_NODE);
        // skip just added calls
        if (!$originalNode instanceof Node) {
            return \true;
        }
        if (!$this->isObjectType($methodCall->var, new ObjectType('Symfony\\Component\\Form\\Form'))) {
            return \true;
        }
        if (!$this->isName($methodCall->name, 'isValid')) {
            return \true;
        }
        $previousNode = $methodCall->getAttribute(AttributeKey::PREVIOUS_NODE);
        if ($previousNode !== null) {
            return \true;
        }
        $variableName = $this->getName($methodCall->var);
        return $variableName === null;
    }
    private function isIsSubmittedByAlreadyCalledOnVariable(Variable $variable) : bool
    {
        $previousMethodCallNamesOnVariable = $this->methodCallManipulator->findMethodCallNamesOnVariable($variable);
        // already checked by isSubmitted()
        return \in_array('isSubmitted', $previousMethodCallNamesOnVariable, \true);
    }
}

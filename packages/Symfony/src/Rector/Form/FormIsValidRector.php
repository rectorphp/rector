<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class FormIsValidRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @var string
     */
    private $formClass;

    public function __construct(
        MethodCallNodeFactory $methodCallNodeFactory,
        MethodCallAnalyzer $methodCallAnalyzer,
        string $formClass = 'Symfony\Component\Form\Form'
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->formClass = $formClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Adds `$form->isSubmitted()` validatoin to all `$form->isValid()` calls in Form in Symfony',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
if ($form->isValid()) { 
}
CODE_SAMPLE
                    ,
<<<'CODE_SAMPLE'
if ($form->isSubmitted() && $form->isValid()) {
}
CODE_SAMPLE
                ),
            ]
        );
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
        // skip just added calls
        if ($node->getAttribute(Attribute::ORIGINAL_NODE) === null) {
            return null;
        }
        if (! $this->methodCallAnalyzer->isTypeAndMethod($node, $this->formClass, 'isValid')) {
            return null;
        }
        if (($node->getAttribute(Attribute::PREVIOUS_NODE) === null) === false) {
            return null;
        }
        /** @var Variable $variableNode */
        $variableNode = $node->var;
        $variableName = (string) $variableNode->name;

        return new BooleanAnd(
            $this->methodCallNodeFactory->createWithVariableNameAndMethodName($variableName, 'isSubmitted'),
            $this->methodCallNodeFactory->createWithVariableNameAndMethodName($variableName, 'isValid')
        );
    }
}

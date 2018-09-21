<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Symfony\Rector\Form\Helper\FormTypeStringToTypeProvider;

/**
 * Covers https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#frameworkbundle
 */
final class StringFormTypeToClassRector extends AbstractRector
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var FormTypeStringToTypeProvider
     */
    private $formTypeStringToTypeProvider;

    /**
     * @var string
     */
    private $formBuilderClass;

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    public function __construct(
        NodeFactory $nodeFactory,
        FormTypeStringToTypeProvider $formTypeStringToTypeProvider,
        MethodCallAnalyzer $methodCallAnalyzer,
        string $formBuilderClass = 'Symfony\Component\Form\FormBuilderInterface'
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->formBuilderClass = $formBuilderClass;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
    }

    /**
     * @todo add custom form types
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony',
            [
                new CodeSample(
<<<'CODE_SAMPLE'
$formBuilder = new Symfony\Component\Form\FormBuilder;
$formBuilder->add('name', 'form.type.text');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$formBuilder = new Symfony\Component\Form\FormBuilder;
$form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
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
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        if (! $this->methodCallAnalyzer->isTypeAndMethod($methodCallNode, $this->formBuilderClass, 'add')) {
            return null;
        }

        // just one argument
        if (! isset($methodCallNode->args[1])) {
            return null;
        }

        // not a string
        if (! $methodCallNode->args[1]->value instanceof String_) {
            return null;
        }

        /** @var String_ $stringNode */
        $stringNode = $methodCallNode->args[1]->value;

        // not a form type string
        $formClass = $this->formTypeStringToTypeProvider->matchClassForNameWithPrefix($stringNode->value);
        if ($formClass === null) {
            return null;
        }

        $methodCallNode->args[1]->value = $this->nodeFactory->createClassConstantReference($formClass);

        return $methodCallNode;
    }
}

<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Node\NodeFactory;
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

    public function __construct(
        NodeFactory $nodeFactory,
        FormTypeStringToTypeProvider $formTypeStringToTypeProvider,
        string $formBuilderClass = 'Symfony\Component\Form\FormBuilderInterface'
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->formBuilderClass = $formBuilderClass;
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
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, $this->formBuilderClass)) {
            return null;
        }

        if (! $this->isName($node, 'add')) {
            return null;
        }

        // just one argument
        if (! isset($node->args[1])) {
            return null;
        }

        // not a string
        if (! $node->args[1]->value instanceof String_) {
            return null;
        }

        /** @var String_ $stringNode */
        $stringNode = $node->args[1]->value;

        // not a form type string
        $formClass = $this->formTypeStringToTypeProvider->matchClassForNameWithPrefix($stringNode->value);
        if ($formClass === null) {
            return null;
        }

        $node->args[1]->value = $this->nodeFactory->createClassConstantReference($formClass);

        return $node;
    }
}

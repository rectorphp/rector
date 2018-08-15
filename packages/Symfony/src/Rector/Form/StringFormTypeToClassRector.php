<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Form;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Node\NodeFactory;
use Rector\NodeTypeResolver\Node\Attribute;
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

    public function __construct(NodeFactory $nodeFactory, FormTypeStringToTypeProvider $formTypeStringToTypeProvider)
    {
        $this->nodeFactory = $nodeFactory;
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
                    '$form->add("name", "form.type.text");',
                    '$form->add("name", \Symfony\Component\Form\Extension\Core\Type\TextType::class);'
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    /**
     * @param String_ $stringNode
     */
    public function refactor(Node $stringNode): ?Node
    {
        if (! $this->formTypeStringToTypeProvider->hasClassForNameWithPrefix($stringNode->value)) {
            return null;
        }
        if (((string) $stringNode->getAttribute(Attribute::METHOD_CALL_NAME) === 'add') === false) {
            return null;
        }
        $class = $this->formTypeStringToTypeProvider->getClassForNameWithPrefix($stringNode->value);

        return $this->nodeFactory->createClassConstantReference($class);
    }
}

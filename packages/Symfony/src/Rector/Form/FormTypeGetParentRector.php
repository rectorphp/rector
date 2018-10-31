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

final class FormTypeGetParentRector extends AbstractRector
{
    /**
     * @var string
     */
    private $abstractTypeClass;

    /**
     * @var string
     */
    private $abstractTypeExtensionClass;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var FormTypeStringToTypeProvider
     */
    private $formTypeStringToTypeProvider;

    public function __construct(
        NodeFactory $nodeFactory,
        FormTypeStringToTypeProvider $formTypeStringToTypeProvider,
        string $abstractTypeClass = 'Symfony\Component\Form\AbstractType',
        string $abstractTypeExtensionClass = 'Symfony\Component\Form\AbstractTypeExtension'
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
        $this->abstractTypeClass = $abstractTypeClass;
        $this->abstractTypeExtensionClass = $abstractTypeExtensionClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns string Form Type references to their CONSTANT alternatives in `getParent()` and `getExtendedType()` methods in Form in Symfony',
            [
                new CodeSample(
                    'function getParent() { return "collection"; }',
                    'function getParent() { return CollectionType::class; }'
                ),
                new CodeSample(
                    'function getExtendedType() { return "collection"; }',
                    'function getExtendedType() { return CollectionType::class; }'
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
     * @param String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $formClass = $this->formTypeStringToTypeProvider->matchClassForNameWithPrefix($node->value);
        if ($formClass === null) {
            return null;
        }

        if (! $this->isParentTypeAndMethod($node, $this->abstractTypeClass, 'getParent') &&
            ! $this->isParentTypeAndMethod($node, $this->abstractTypeExtensionClass, 'getExtendedType')
        ) {
            return null;
        }

        return $this->nodeFactory->createClassConstantReference($formClass);
    }

    private function isParentTypeAndMethod(Node $node, string $type, string $method): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== $type) {
            return false;
        }

        return $node->getAttribute(Attribute::METHOD_NAME) === $method;
    }
}

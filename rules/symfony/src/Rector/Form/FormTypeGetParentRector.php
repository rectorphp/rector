<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Form;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\FormHelper\FormTypeStringToTypeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\AbstractTypeExtension;

/**
 * @see \Rector\Symfony\Tests\Rector\Form\FormTypeGetParentRector\FormTypeGetParentRectorTest
 */
final class FormTypeGetParentRector extends AbstractRector
{
    /**
     * @var FormTypeStringToTypeProvider
     */
    private $formTypeStringToTypeProvider;

    public function __construct(FormTypeStringToTypeProvider $formTypeStringToTypeProvider)
    {
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
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

        if (! $this->isParentTypeAndMethod($node, AbstractType::class, 'getParent') &&
            ! $this->isParentTypeAndMethod($node, AbstractTypeExtension::class, 'getExtendedType')
        ) {
            return null;
        }

        return $this->createClassConstantReference($formClass);
    }

    private function isParentTypeAndMethod(Node $node, string $type, string $method): bool
    {
        $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName !== $type) {
            return false;
        }

        return $node->getAttribute(AttributeKey::METHOD_NAME) === $method;
    }
}

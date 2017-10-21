<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Form;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\Rector\Contrib\Symfony\Form\Helper\FormTypeStringToTypeProvider;

/**
 * Converts all:
 * - getParent() {
 *      return 'collection';
 * }
 * - getExtendedType() {
 *      return 'collection';
 * }
 *
 * into:
 * - getParent() {
 *      return CollectionType::class;
 * }
 * - getExtendedType() {
 *      return CollectionType::class;
 * }
 */
final class FormTypeGetParentRector extends AbstractRector
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

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof String_) {
            return false;
        }

        if (! $this->formTypeStringToTypeProvider->hasClassForName($node->value)) {
            return false;
        }

        if ($this->isParentTypeAndMethod($node, 'Symfony\Component\Form\AbstractType', 'getParent')) {
            return true;
        }

        if ($this->isParentTypeAndMethod($node, 'Symfony\Component\Form\AbstractTypeExtension', 'getExtendedType')) {
            return true;
        }

        return false;
    }

    /**
     * @param String_ $stringNode
     */
    public function refactor(Node $stringNode): ?Node
    {
        $class = $this->formTypeStringToTypeProvider->getClassForName($stringNode->value);

        return $this->nodeFactory->createClassConstantReference($class);
    }

    private function isParentTypeAndMethod(Node $node, string $type, string $method): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== $type) {
            return false;
        }

        $methodName = $node->getAttribute(Attribute::METHOD_NAME);

        return $methodName === $method;
    }
}

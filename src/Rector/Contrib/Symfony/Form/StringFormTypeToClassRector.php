<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Form;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\Rector\Contrib\Symfony\Form\Helper\FormTypeStringToTypeProvider;

/**
 * Converts all:
 * $form->add('name', 'form.type.text');
 *
 * into:
 * $form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
 *
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#frameworkbundle
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
    private $formTypeStringToType;

    public function __construct(NodeFactory $nodeFactory, FormTypeStringToTypeProvider $formTypeStringToType)
    {
        $this->nodeFactory = $nodeFactory;
        $this->formTypeStringToType = $formTypeStringToType;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof String_) {
            return false;
        }

        if (! $this->formTypeStringToType->hasClassForNameWithPrefix($node->value)) {
            return false;
        }

        $argNode = $node->getAttribute(Attribute::PARENT_NODE);
        if (! $argNode instanceof Arg) {
            return false;
        }

        $methodCallNode = $argNode->getAttribute(Attribute::PARENT_NODE);
        if (! $methodCallNode instanceof MethodCall) {
            return false;
        }

        return $methodCallNode->name->toString() === 'add';
    }

    /**
     * @param String_ $stringNode
     */
    public function refactor(Node $stringNode): ?Node
    {
        $class = $this->formTypeStringToType->getClassForNameWithPrefix($stringNode->value);

        return $this->nodeFactory->createClassConstantReference($class);
    }
}

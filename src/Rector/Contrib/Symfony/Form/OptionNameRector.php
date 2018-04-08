<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class OptionNameRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewOption = [
        'precision' => 'scale',
        'virtual' => 'inherit_data',
    ];

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns old option names to new ones in FormTypes in Form in Symfony', [
            new CodeSample(
                '$builder->add("...", ["precision" => "...", "virtual" => "..."];',
                '$builder->add("...", ["scale" => "...", "inherit_data" => "..."];'
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof String_) {
            return false;
        }

        if (! isset($this->oldToNewOption[$node->value])) {
            return false;
        }

        $arrayItemParentNode = $node->getAttribute(Attribute::PARENT_NODE);
        if (! $arrayItemParentNode instanceof ArrayItem) {
            return false;
        }

        $arrayParentNode = $arrayItemParentNode->getAttribute(Attribute::PARENT_NODE);
        $argParentNode = $arrayParentNode->getAttribute(Attribute::PARENT_NODE);

        /** @var MethodCall $argParentNode */
        $methodCallNode = $argParentNode->getAttribute(Attribute::PARENT_NODE);

        /** @var MethodCall|Node $methodCallNode */
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
        return $this->nodeFactory->createString($this->oldToNewOption[$stringNode->value]);
    }
}

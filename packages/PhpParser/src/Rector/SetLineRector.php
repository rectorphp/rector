<?php declare(strict_types=1);

namespace Rector\PhpParser\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SetLineRector extends AbstractRector
{
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
        return new RectorDefinition('Turns standalone line method to attribute in Node of PHP-Parser', [
            new CodeSample('$node->setLine(5);', '$node->setAttribute("line", 5);'),
        ]);
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
        if (! $this->isType($node, 'PhpParser\Node')) {
            return null;
        }

        if (! $this->isName($node, 'setLine')) {
            return null;
        }

        $node->name = new Identifier('setAttribute');

        $node->args[1] = $node->args[0];
        $node->args[0] = $this->nodeFactory->createArg(new String_('line'));

        return $node;
    }
}

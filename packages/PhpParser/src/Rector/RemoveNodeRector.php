<?php declare(strict_types=1);

namespace Rector\PhpParser\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\Return_;
use Rector\Node\NodeFactory;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Covers: https://github.com/nikic/PHP-Parser/commit/987c61e935a7d73485b4d73aef7a17a4c1e2e325
 */
final class RemoveNodeRector extends AbstractRector
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
        return new RectorDefinition('Turns integer return to remove node to constant in NodeVisitor of PHP-Parser', [
            new CodeSample(
                <<<'CODE_SAMPLE'
public function leaveNode()
{
    return false;
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
public function leaveNode()
{
    return NodeTraverser::REMOVE_NODE;
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_ $returnNode
     */
    public function refactor(Node $returnNode): ?Node
    {
        if (! $returnNode->expr instanceof ConstFetch) {
            return null;
        }

        $methodName = $returnNode->getAttribute(Attribute::METHOD_NAME);
        if ($methodName !== 'leaveNode') {
            return null;
        }

        $value = $returnNode->expr->name->toString();
        if ($value !== 'false') {
            return null;
        }

        $returnNode->expr = $this->nodeFactory->createClassConstant('PhpParser\NodeTraverser', 'REMOVE_NODE');

        return $returnNode;
    }
}

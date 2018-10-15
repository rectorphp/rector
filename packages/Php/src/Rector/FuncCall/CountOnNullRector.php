<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/Bndc9
 */
final class CountOnNullRector extends AbstractRector
{
    /**
     * @var NodeTypeAnalyzer
     */
    private $nodeTypeAnalyzer;

    public function __construct(NodeTypeAnalyzer $nodeTypeAnalyzer)
    {
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes count() on null to safe ternary check',
            [new CodeSample(
<<<'CODE_SAMPLE'
$values = null;
$count = count($values);
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$values = null;
$count = is_array($values) || $values instanceof Countable ? count($values) : 0;
CODE_SAMPLE
            )]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'count')) {
            return $node;
        }

        // check if it has some condition before already, if so, probably it's already handled
        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode instanceof Ternary) {
            return $node;
        }

        if (! isset($node->args[0])) {
            return $node;
        }

        $countedNode = $node->args[0]->value;

        if ($this->nodeTypeAnalyzer->isCountableType($countedNode)) {
            return $node;
        }

        $conditionNode = new BooleanOr(
            new FuncCall(new Name('is_array'), [new Arg($countedNode)]),
            new Instanceof_($countedNode, new FullyQualified('Countable'))
        );

        $ternaryNode = new Ternary($conditionNode, $node, new LNumber(0));

        // needed to prevent infinity loop re-resolution
        $node->setAttribute(Attribute::PARENT_NODE, $ternaryNode);

        return $ternaryNode;
    }
}

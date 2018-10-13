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
use Rector\NodeAnalyzer\FuncCallAnalyzer;
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

    /**
     * @var FuncCallAnalyzer
     */
    private $funcCallAnalyzer;

    public function __construct(NodeTypeAnalyzer $nodeTypeAnalyzer, FuncCallAnalyzer $funcCallAnalyzer)
    {
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
        $this->funcCallAnalyzer = $funcCallAnalyzer;
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
     * @param FuncCall $funcCallNode
     */
    public function refactor(Node $funcCallNode): ?Node
    {
        if (! $this->funcCallAnalyzer->isName($funcCallNode, 'count')) {
            return $funcCallNode;
        }

        // check if it has some condition before already, if so, probably it's already handled
        $parentNode = $funcCallNode->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode instanceof Ternary) {
            return $funcCallNode;
        }

        if (! isset($funcCallNode->args[0])) {
            return $funcCallNode;
        }

        $countedNode = $funcCallNode->args[0]->value;

        if ($this->nodeTypeAnalyzer->isCountableType($countedNode)) {
            return $funcCallNode;
        }

        $conditionNode = new BooleanOr(
            new FuncCall(new Name('is_array'), [new Arg($countedNode)]),
            new Instanceof_($countedNode, new FullyQualified('Countable'))
        );

        $ternaryNode = new Ternary($conditionNode, $funcCallNode, new LNumber(0));

        // needed to prevent infinity loop re-resolution
        $funcCallNode->setAttribute(Attribute::PARENT_NODE, $ternaryNode);

        return $ternaryNode;
    }
}

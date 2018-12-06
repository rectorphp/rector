<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\NodeTransformer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Util\RectorStrings;

/**
 * @see https://github.com/symfony/symfony/pull/27821/files
 */
final class StringToArrayArgumentProcessRector extends AbstractRector
{
    /**
     * @var string
     */
    private $processClass;

    /**
     * @var string
     */
    private $processHelperClass;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeTransformer
     */
    private $nodeTransformer;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NodeTransformer $nodeTransformer,
        string $processClass = 'Symfony\Component\Process\Process',
        string $processHelperClass = 'Symfony\Component\Console\Helper\ProcessHelper'
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTransformer = $nodeTransformer;
        $this->processClass = $processClass;
        $this->processHelperClass = $processHelperClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes Process string argument to an array', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\Process\Process;
$process = new Process('ls -l');
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symfony\Component\Process\Process;
$process = new Process(['ls', '-l']);
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [New_::class, MethodCall::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isType($node, $this->processClass)) {
            return $this->processArgumentPosition($node, 0);
        }

        if ($this->isType($node, $this->processHelperClass)) {
            return $this->processArgumentPosition($node, 1);
        }

        return null;
    }

    /**
     * @param New_|MethodCall $node
     */
    private function processArgumentPosition(Node $node, int $argumentPosition): ?Node
    {
        if (! isset($node->args[$argumentPosition])) {
            return null;
        }

        $firstArgument = $node->args[$argumentPosition]->value;
        if ($firstArgument instanceof Array_) {
            return null;
        }

        if ($firstArgument instanceof String_) {
            $parts = RectorStrings::splitCommandToItems($firstArgument->value);
            $node->args[$argumentPosition]->value = $this->createArray($parts);
        }

        // type analyzer
        if ($this->isStringType($firstArgument)) {
            $this->processStringType($node, $argumentPosition, $firstArgument);
        }

        return $node;
    }

    private function findPreviousNodeAssign(Node $node, Node $firstArgument): ?Assign
    {
        return $this->betterNodeFinder->findFirstPrevious($node, function (Node $checkedNode) use ($firstArgument) {
            if (! $checkedNode instanceof Assign) {
                return null;
            }

            if (! $this->areNodesEqual($checkedNode->var, $firstArgument)) {
                return null;
            }

            // @todo check out of scope assign, e.g. in previous method

            return $checkedNode;
        });
    }

    /**
     * @param New_|MethodCall $node
     */
    private function processStringType(Node $node, int $argumentPosition, Node $firstArgument): void
    {
        if ($firstArgument instanceof Concat) {
            $arrayNode = $this->nodeTransformer->transformConcatToStringArray($firstArgument);
            if ($arrayNode) {
                $node->args[$argumentPosition] = new Arg($arrayNode);
            }
        }

        /** @var Assign|null $createdNode */
        $createdNode = $this->findPreviousNodeAssign($node, $firstArgument);
        if ($createdNode === null) {
            return;
        }

        if (! $createdNode->expr instanceof FuncCall || ! $this->isName($createdNode->expr, 'sprintf')) {
            return;
        }

        $arrayNode = $this->nodeTransformer->transformSprintfToArray($createdNode->expr);
        if ($arrayNode) {
            $createdNode->expr = $arrayNode;
        }
    }
}

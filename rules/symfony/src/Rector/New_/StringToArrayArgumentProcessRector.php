<?php

declare(strict_types=1);

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
use PHPStan\Type\StringType;
use Rector\Core\PhpParser\NodeTransformer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Process\Process;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

/**
 * @see https://github.com/symfony/symfony/pull/27821/files
 * @see \Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\StringToArrayArgumentProcessRectorTest
 */
final class StringToArrayArgumentProcessRector extends AbstractRector
{
    /**
     * @var NodeTransformer
     */
    private $nodeTransformer;

    public function __construct(NodeTransformer $nodeTransformer)
    {
        $this->nodeTransformer = $nodeTransformer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes Process string argument to an array', [
            new CodeSample(
                <<<'PHP'
use Symfony\Component\Process\Process;
$process = new Process('ls -l');
PHP
                ,
                <<<'PHP'
use Symfony\Component\Process\Process;
$process = new Process(['ls', '-l']);
PHP
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
     * @param New_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $expr = $node instanceof New_ ? $node->class : $node->var;

        if ($this->isObjectType($expr, Process::class)) {
            return $this->processArgumentPosition($node, 0);
        }

        if ($this->isObjectType($expr, ProcessHelper::class)) {
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

        // type analyzer
        if ($this->isStaticType($firstArgument, StringType::class)) {
            $this->processStringType($node, $argumentPosition, $firstArgument);
        }

        return $node;
    }

    /**
     * @param New_|MethodCall $node
     */
    private function processStringType(Node $node, int $argumentPosition, Node $firstArgument): void
    {
        if ($firstArgument instanceof Concat) {
            $arrayNode = $this->nodeTransformer->transformConcatToStringArray($firstArgument);
            if ($arrayNode !== null) {
                $node->args[$argumentPosition] = new Arg($arrayNode);
            }

            return;
        }

        if ($this->isFuncCallName($firstArgument, 'sprintf')) {
            /** @var FuncCall $firstArgument */
            $arrayNode = $this->nodeTransformer->transformSprintfToArray($firstArgument);
            if ($arrayNode !== null) {
                $node->args[$argumentPosition]->value = $arrayNode;
            }
        } elseif ($firstArgument instanceof String_) {
            $parts = $this->splitProcessCommandToItems($firstArgument->value);
            $node->args[$argumentPosition]->value = $this->createArray($parts);
        }

        $this->processPreviousAssign($node, $firstArgument);
    }

    private function processPreviousAssign(Node $node, Node $firstArgument): void
    {
        /** @var Assign|null $createdNode */
        $createdNode = $this->findPreviousNodeAssign($node, $firstArgument);

        if ($createdNode instanceof Assign && $this->isFuncCallName($createdNode->expr, 'sprintf')) {
            /** @var FuncCall $funcCall */
            $funcCall = $createdNode->expr;
            $arrayNode = $this->nodeTransformer->transformSprintfToArray($funcCall);
            if ($arrayNode !== null) {
                $createdNode->expr = $arrayNode;
            }
        }
    }

    private function findPreviousNodeAssign(Node $node, Node $firstArgument): ?Assign
    {
        return $this->betterNodeFinder->findFirstPrevious($node, function (Node $checkedNode) use (
            $firstArgument
        ): ?Assign {
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
     * @return string[]
     */
    private function splitProcessCommandToItems(string $process): array
    {
        $privatesCaller = new PrivatesCaller();
        return $privatesCaller->callPrivateMethod(new StringInput(''), 'tokenize', $process);
    }
}

<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Core\PhpParser\NodeTransformer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\Reflection\PrivatesAccessor;
use RectorPrefix202304\Symfony\Component\Console\Input\StringInput;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/pull/27821/files
 * @see \Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\StringToArrayArgumentProcessRectorTest
 */
final class StringToArrayArgumentProcessRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const EXCLUDED_PROCESS_METHOD_CALLS = ['setWorkingDirectory', 'addOutput', 'addErrorOutput'];
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeTransformer
     */
    private $nodeTransformer;
    public function __construct(NodeTransformer $nodeTransformer)
    {
        $this->nodeTransformer = $nodeTransformer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes Process string argument to an array', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Process\Process;
$process = new Process('ls -l');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Process\Process;
$process = new Process(['ls', '-l']);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [New_::class, MethodCall::class];
    }
    /**
     * @param New_|MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $expr = $node instanceof New_ ? $node->class : $node->var;
        if ($this->isObjectType($expr, new ObjectType('Symfony\\Component\\Process\\Process'))) {
            return $this->processArgumentPosition($node, 0);
        }
        if ($this->isObjectType($expr, new ObjectType('Symfony\\Component\\Console\\Helper\\ProcessHelper'))) {
            return $this->processArgumentPosition($node, 1);
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\MethodCall $node
     */
    private function processArgumentPosition($node, int $argumentPosition) : ?Node
    {
        if (!isset($node->args[$argumentPosition])) {
            return null;
        }
        $activeArg = $node->args[$argumentPosition];
        if (!$activeArg instanceof Arg) {
            return null;
        }
        $activeArgValue = $activeArg->value;
        if ($activeArgValue instanceof Array_) {
            return null;
        }
        if ($node instanceof MethodCall && $this->shouldSkipProcessMethodCall($node)) {
            return null;
        }
        // type analyzer
        $activeValueType = $this->getType($activeArgValue);
        if (!$activeValueType instanceof StringType) {
            return null;
        }
        $this->processStringType($node, $argumentPosition, $activeArgValue);
        return $node;
    }
    private function shouldSkipProcessMethodCall(MethodCall $methodCall) : bool
    {
        $methodName = (string) $this->nodeNameResolver->getName($methodCall->name);
        return \in_array($methodName, self::EXCLUDED_PROCESS_METHOD_CALLS, \true);
    }
    /**
     * @param \PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\MethodCall $expr
     */
    private function processStringType($expr, int $argumentPosition, Expr $firstArgumentExpr) : void
    {
        if ($firstArgumentExpr instanceof Concat) {
            $arrayNode = $this->nodeTransformer->transformConcatToStringArray($firstArgumentExpr);
            $expr->args[$argumentPosition] = new Arg($arrayNode);
            return;
        }
        $args = $expr->getArgs();
        if ($firstArgumentExpr instanceof FuncCall && $this->isName($firstArgumentExpr, 'sprintf')) {
            $arrayNode = $this->nodeTransformer->transformSprintfToArray($firstArgumentExpr);
            if ($arrayNode instanceof Array_) {
                $args[$argumentPosition]->value = $arrayNode;
            }
        } elseif ($firstArgumentExpr instanceof String_) {
            $parts = $this->splitProcessCommandToItems($firstArgumentExpr->value);
            $args[$argumentPosition]->value = $this->nodeFactory->createArray($parts);
        }
        $this->processPreviousAssign($expr, $firstArgumentExpr);
    }
    /**
     * @return string[]
     */
    private function splitProcessCommandToItems(string $process) : array
    {
        $privatesAccessor = new PrivatesAccessor();
        return $privatesAccessor->callPrivateMethod(new StringInput(''), 'tokenize', [$process]);
    }
    private function processPreviousAssign(Node $node, Expr $firstArgumentExpr) : void
    {
        $assign = $this->findPreviousNodeAssign($node, $firstArgumentExpr);
        if (!$assign instanceof Assign) {
            return;
        }
        if (!$assign->expr instanceof FuncCall) {
            return;
        }
        $funcCall = $assign->expr;
        if (!$this->nodeNameResolver->isName($funcCall, 'sprintf')) {
            return;
        }
        $arrayNode = $this->nodeTransformer->transformSprintfToArray($funcCall);
        if ($arrayNode instanceof Array_ && \count($arrayNode->items) > 1) {
            $assign->expr = $arrayNode;
        }
    }
    private function findPreviousNodeAssign(Node $node, Expr $firstArgumentExpr) : ?Assign
    {
        /** @var Assign|null $assign */
        $assign = $this->betterNodeFinder->findFirstPrevious($node, function (Node $checkedNode) use($firstArgumentExpr) : ?Assign {
            if (!$checkedNode instanceof Assign) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($checkedNode->var, $firstArgumentExpr)) {
                return null;
            }
            return $checkedNode;
        });
        return $assign;
    }
}

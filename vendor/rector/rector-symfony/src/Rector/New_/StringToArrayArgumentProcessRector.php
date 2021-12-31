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
use RectorPrefix20211231\Symfony\Component\Console\Input\StringInput;
use RectorPrefix20211231\Symplify\PackageBuilder\Reflection\PrivatesCaller;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/pull/27821/files
 * @see \Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\StringToArrayArgumentProcessRectorTest
 */
final class StringToArrayArgumentProcessRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]
     */
    private const EXCLUDED_PROCESS_METHOD_CALLS = ['setWorkingDirectory', 'addOutput', 'addErrorOutput'];
    /**
     * @var \Rector\Core\PhpParser\NodeTransformer
     */
    private $nodeTransformer;
    public function __construct(\Rector\Core\PhpParser\NodeTransformer $nodeTransformer)
    {
        $this->nodeTransformer = $nodeTransformer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes Process string argument to an array', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\New_::class, \PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param New_|MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $expr = $node instanceof \PhpParser\Node\Expr\New_ ? $node->class : $node->var;
        if ($this->isObjectType($expr, new \PHPStan\Type\ObjectType('Symfony\\Component\\Process\\Process'))) {
            return $this->processArgumentPosition($node, 0);
        }
        if ($this->isObjectType($expr, new \PHPStan\Type\ObjectType('Symfony\\Component\\Console\\Helper\\ProcessHelper'))) {
            return $this->processArgumentPosition($node, 1);
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_ $node
     */
    private function processArgumentPosition($node, int $argumentPosition) : ?\PhpParser\Node
    {
        if (!isset($node->args[$argumentPosition])) {
            return null;
        }
        $activeArg = $node->args[$argumentPosition];
        if (!$activeArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $activeArgValue = $activeArg->value;
        if ($activeArgValue instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall && $this->shouldSkipProcessMethodCall($node)) {
            return null;
        }
        // type analyzer
        $activeValueType = $this->getType($activeArgValue);
        if (!$activeValueType instanceof \PHPStan\Type\StringType) {
            return null;
        }
        $this->processStringType($node, $argumentPosition, $activeArgValue);
        return $node;
    }
    private function shouldSkipProcessMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        $methodName = (string) $this->nodeNameResolver->getName($methodCall->name);
        return \in_array($methodName, self::EXCLUDED_PROCESS_METHOD_CALLS, \true);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_ $expr
     */
    private function processStringType($expr, int $argumentPosition, \PhpParser\Node\Expr $firstArgumentExpr) : void
    {
        if ($firstArgumentExpr instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            $arrayNode = $this->nodeTransformer->transformConcatToStringArray($firstArgumentExpr);
            $expr->args[$argumentPosition] = new \PhpParser\Node\Arg($arrayNode);
            return;
        }
        if ($firstArgumentExpr instanceof \PhpParser\Node\Expr\FuncCall && $this->isName($firstArgumentExpr, 'sprintf')) {
            $arrayNode = $this->nodeTransformer->transformSprintfToArray($firstArgumentExpr);
            if ($arrayNode !== null) {
                $expr->args[$argumentPosition]->value = $arrayNode;
            }
        } elseif ($firstArgumentExpr instanceof \PhpParser\Node\Scalar\String_) {
            $parts = $this->splitProcessCommandToItems($firstArgumentExpr->value);
            $expr->args[$argumentPosition]->value = $this->nodeFactory->createArray($parts);
        }
        $this->processPreviousAssign($expr, $firstArgumentExpr);
    }
    /**
     * @return string[]
     */
    private function splitProcessCommandToItems(string $process) : array
    {
        $privatesCaller = new \RectorPrefix20211231\Symplify\PackageBuilder\Reflection\PrivatesCaller();
        return $privatesCaller->callPrivateMethod(new \RectorPrefix20211231\Symfony\Component\Console\Input\StringInput(''), 'tokenize', [$process]);
    }
    private function processPreviousAssign(\PhpParser\Node $node, \PhpParser\Node\Expr $firstArgumentExpr) : void
    {
        $assign = $this->findPreviousNodeAssign($node, $firstArgumentExpr);
        if (!$assign instanceof \PhpParser\Node\Expr\Assign) {
            return;
        }
        if (!$assign->expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return;
        }
        $funcCall = $assign->expr;
        if (!$this->nodeNameResolver->isName($funcCall, 'sprintf')) {
            return;
        }
        $arrayNode = $this->nodeTransformer->transformSprintfToArray($funcCall);
        if ($arrayNode !== null) {
            $assign->expr = $arrayNode;
        }
    }
    private function findPreviousNodeAssign(\PhpParser\Node $node, \PhpParser\Node\Expr $firstArgumentExpr) : ?\PhpParser\Node\Expr\Assign
    {
        /** @var Assign|null $assign */
        $assign = $this->betterNodeFinder->findFirstPrevious($node, function (\PhpParser\Node $checkedNode) use($firstArgumentExpr) : ?Assign {
            if (!$checkedNode instanceof \PhpParser\Node\Expr\Assign) {
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

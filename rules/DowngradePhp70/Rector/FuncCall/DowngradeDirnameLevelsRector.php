<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\PhpParser\Node\NamedVariableFactory;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://bugs.php.net/bug.php?id=70112
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeDirnameLevelsRector\DowngradeDirnameLevelsRectorTest
 */
final class DowngradeDirnameLevelsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const DIRNAME = 'dirname';
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NamedVariableFactory
     */
    private $namedVariableFactory;
    public function __construct(\Rector\Core\PhpParser\Node\NamedVariableFactory $namedVariableFactory)
    {
        $this->namedVariableFactory = $namedVariableFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace the 2nd argument of dirname()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return dirname($path, 2);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return dirname(dirname($path));
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $levelsArg = $this->getLevelsArg($node);
        if (!$levelsArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $levels = $this->getLevelsRealValue($levelsArg);
        if ($levels !== null) {
            return $this->refactorForFixedLevels($node, $levels);
        }
        return $this->refactorForVariableLevels($node);
    }
    private function getLevelsArg(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Arg
    {
        if (!$this->isName($funcCall, self::DIRNAME)) {
            return null;
        }
        if (!isset($funcCall->args[1])) {
            return null;
        }
        if (!$funcCall->args[1] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        return $funcCall->args[1];
    }
    private function getLevelsRealValue(\PhpParser\Node\Arg $levelsArg) : ?int
    {
        if ($levelsArg->value instanceof \PhpParser\Node\Scalar\LNumber) {
            return $levelsArg->value->value;
        }
        return null;
    }
    private function refactorForFixedLevels(\PhpParser\Node\Expr\FuncCall $funcCall, int $levels) : \PhpParser\Node\Expr\FuncCall
    {
        // keep only the 1st argument
        $funcCall->args = [$funcCall->args[0]];
        for ($i = 1; $i < $levels; ++$i) {
            $funcCall = $this->createDirnameFuncCall(new \PhpParser\Node\Arg($funcCall));
        }
        return $funcCall;
    }
    private function refactorForVariableLevels(\PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\FuncCall
    {
        $funcVariable = $this->namedVariableFactory->createVariable($funcCall, 'dirnameFunc');
        $closure = $this->createClosure();
        $exprAssignClosure = $this->createExprAssign($funcVariable, $closure);
        $this->nodesToAddCollector->addNodeBeforeNode($exprAssignClosure, $funcCall, $this->file->getSmartFileInfo());
        $funcCall->name = $funcVariable;
        return $funcCall;
    }
    private function createExprAssign(\PhpParser\Node\Expr\Variable $variable, \PhpParser\Node\Expr $expr) : \PhpParser\Node\Stmt\Expression
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($variable, $expr));
    }
    private function createClosure() : \PhpParser\Node\Expr\Closure
    {
        $dirVariable = new \PhpParser\Node\Expr\Variable('dir');
        $pathVariable = new \PhpParser\Node\Expr\Variable('path');
        $levelsVariable = new \PhpParser\Node\Expr\Variable('levels');
        $closure = new \PhpParser\Node\Expr\Closure();
        $closure->params = [new \PhpParser\Node\Param($pathVariable), new \PhpParser\Node\Param($levelsVariable)];
        $closure->stmts[] = $this->createExprAssign($dirVariable, $this->nodeFactory->createNull());
        $greaterOrEqual = new \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual(new \PhpParser\Node\Expr\PreDec($levelsVariable), new \PhpParser\Node\Scalar\LNumber(0));
        $closure->stmts[] = new \PhpParser\Node\Stmt\While_($greaterOrEqual, [$this->createExprAssign($dirVariable, $this->createDirnameFuncCall(new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\Ternary($dirVariable, null, $pathVariable))))]);
        $closure->stmts[] = new \PhpParser\Node\Stmt\Return_($dirVariable);
        return $closure;
    }
    private function createDirnameFuncCall(\PhpParser\Node\Arg $pathArg) : \PhpParser\Node\Expr\FuncCall
    {
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name(self::DIRNAME), [$pathArg]);
    }
}

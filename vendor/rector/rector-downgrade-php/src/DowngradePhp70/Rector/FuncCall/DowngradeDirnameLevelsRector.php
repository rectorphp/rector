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
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://bugs.php.net/bug.php?id=70112
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeDirnameLevelsRector\DowngradeDirnameLevelsRectorTest
 */
final class DowngradeDirnameLevelsRector extends AbstractRector
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
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(NamedVariableFactory $namedVariableFactory, NodesToAddCollector $nodesToAddCollector)
    {
        $this->namedVariableFactory = $namedVariableFactory;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace the 2nd argument of dirname()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $levelsArg = $this->getLevelsArg($node);
        if (!$levelsArg instanceof Arg) {
            return null;
        }
        $levels = $this->getLevelsRealValue($levelsArg);
        if ($levels !== null) {
            return $this->refactorForFixedLevels($node, $levels);
        }
        return $this->refactorForVariableLevels($node);
    }
    private function getLevelsArg(FuncCall $funcCall) : ?Arg
    {
        if (!$this->isName($funcCall, self::DIRNAME)) {
            return null;
        }
        if (!isset($funcCall->args[1])) {
            return null;
        }
        if (!$funcCall->args[1] instanceof Arg) {
            return null;
        }
        return $funcCall->args[1];
    }
    private function getLevelsRealValue(Arg $levelsArg) : ?int
    {
        if ($levelsArg->value instanceof LNumber) {
            return $levelsArg->value->value;
        }
        return null;
    }
    private function refactorForFixedLevels(FuncCall $funcCall, int $levels) : FuncCall
    {
        // keep only the 1st argument
        $funcCall->args = [$funcCall->args[0]];
        for ($i = 1; $i < $levels; ++$i) {
            $funcCall = $this->createDirnameFuncCall(new Arg($funcCall));
        }
        return $funcCall;
    }
    private function refactorForVariableLevels(FuncCall $funcCall) : FuncCall
    {
        $funcVariable = $this->namedVariableFactory->createVariable($funcCall, 'dirnameFunc');
        $closure = $this->createClosure();
        $exprAssignClosure = $this->createExprAssign($funcVariable, $closure);
        $this->nodesToAddCollector->addNodeBeforeNode($exprAssignClosure, $funcCall);
        $funcCall->name = $funcVariable;
        return $funcCall;
    }
    private function createExprAssign(Variable $variable, Expr $expr) : Expression
    {
        return new Expression(new Assign($variable, $expr));
    }
    private function createClosure() : Closure
    {
        $dirVariable = new Variable('dir');
        $pathVariable = new Variable('path');
        $levelsVariable = new Variable('levels');
        $closure = new Closure();
        $closure->params = [new Param($pathVariable), new Param($levelsVariable)];
        $closure->stmts[] = $this->createExprAssign($dirVariable, $this->nodeFactory->createNull());
        $greaterOrEqual = new GreaterOrEqual(new PreDec($levelsVariable), new LNumber(0));
        $closure->stmts[] = new While_($greaterOrEqual, [$this->createExprAssign($dirVariable, $this->createDirnameFuncCall(new Arg(new Ternary($dirVariable, null, $pathVariable))))]);
        $closure->stmts[] = new Return_($dirVariable);
        return $closure;
    }
    private function createDirnameFuncCall(Arg $pathArg) : FuncCall
    {
        return new FuncCall(new Name(self::DIRNAME), [$pathArg]);
    }
}

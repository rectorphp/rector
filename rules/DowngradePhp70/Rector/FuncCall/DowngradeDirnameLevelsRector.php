<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp70\Rector\FuncCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\PreDec;
use RectorPrefix20220606\PhpParser\Node\Expr\Ternary;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\PhpParser\Node\Stmt\While_;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NamedVariableFactory;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
    public function __construct(NamedVariableFactory $namedVariableFactory)
    {
        $this->namedVariableFactory = $namedVariableFactory;
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
        $this->nodesToAddCollector->addNodeBeforeNode($exprAssignClosure, $funcCall, $this->file->getSmartFileInfo());
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

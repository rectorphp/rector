<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeProcOpenArrayCommandArgRector\DowngradeProcOpenArrayCommandArgRectorTest
 */
final class DowngradeProcOpenArrayCommandArgRector extends AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(NodesToAddCollector $nodesToAddCollector, VariableNaming $variableNaming)
    {
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->variableNaming = $variableNaming;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change array command argument on proc_open to implode spaced string', [new CodeSample(<<<'CODE_SAMPLE'
function (array|string $command)
{
    $process = proc_open($command, $descriptorspec, $pipes, null, null, ['suppress_errors' => true]);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function (array|string $command)
{
    if (is_array($command)) {
        $command = implode(" ", $command);
    }

    $process = proc_open($command, $descriptorspec, $pipes, null, null, ['suppress_errors' => true]);
}
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
    public function refactorWithScope(Node $node, Scope $scope) : ?FuncCall
    {
        if (!$this->isName($node, 'proc_open')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[0])) {
            return null;
        }
        $commandType = $this->getType($args[0]->value);
        if ($commandType->isString()->yes()) {
            return null;
        }
        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($node);
        if (!$currentStmt instanceof Stmt) {
            return null;
        }
        $variable = $args[0]->value instanceof Variable ? $args[0]->value : new Variable($this->variableNaming->createCountedValueName('command', $scope));
        if ($args[0]->value !== $variable) {
            $assign = new Assign($variable, $args[0]->value);
            $this->nodesToAddCollector->addNodeBeforeNode(new Expression($assign), $currentStmt);
            $node->args[0] = new Arg($variable);
        }
        $implode = $this->nodeFactory->createFuncCall('implode', [new String_(' '), $variable]);
        $this->nodesToAddCollector->addNodeBeforeNode(new If_($this->nodeFactory->createFuncCall('is_array', [$variable]), ['stmts' => [new Expression(new Assign($variable, $implode))]]), $currentStmt);
        return $node;
    }
}

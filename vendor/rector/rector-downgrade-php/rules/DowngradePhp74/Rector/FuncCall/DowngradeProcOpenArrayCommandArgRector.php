<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeProcOpenArrayCommandArgRector\DowngradeProcOpenArrayCommandArgRectorTest
 */
final class DowngradeProcOpenArrayCommandArgRector extends AbstractScopeAwareRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change array command argument on proc_open to implode spaced string', [new CodeSample(<<<'CODE_SAMPLE'
return proc_open($command, $descriptorspec, $pipes);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return proc_open(is_array($command) ? implode(' ', $command) : $command, $descriptorspec, $pipes);
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
        $firstArg = $node->getArgs()[0];
        $commandType = $this->getType($firstArg->value);
        if ($commandType->isString()->yes()) {
            return null;
        }
        $isArrayFuncCall = $this->nodeFactory->createFuncCall('is_array', [new Arg($firstArg->value)]);
        $implodeFuncCall = $this->nodeFactory->createFuncCall('implode', [new String_(' '), $firstArg->value]);
        $firstArg->value = new Ternary($isArrayFuncCall, $implodeFuncCall, $firstArg->value);
        return $node;
    }
}

<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\CodingStyle\NodeAnalyzer\SpreadVariablesCollector;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\UnSpreadOperatorRectorTest
 */
final class UnSpreadOperatorRector extends AbstractRector
{
    /**
     * @var SpreadVariablesCollector
     */
    private $spreadVariablesCollector;

    public function __construct(SpreadVariablesCollector $spreadVariablesCollector)
    {
        $this->spreadVariablesCollector = $spreadVariablesCollector;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove spread operator', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(...$array)
    {
    }

    public function execute(array $data)
    {
        $this->run(...$data);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $array)
    {
    }

    public function execute(array $data)
    {
        $this->run($data);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, MethodCall::class];
    }

    /**
     * @param ClassMethod|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->processUnspreadOperatorClassMethodParams($node);
        }

        return $this->processUnspreadOperatorMethodCallArgs($node);
    }

    private function processUnspreadOperatorClassMethodParams(ClassMethod $classMethod): ?ClassMethod
    {
        $spreadParams = $this->spreadVariablesCollector->resolveFromClassMethod($classMethod);
        if ($spreadParams === []) {
            return null;
        }

        foreach ($spreadParams as $spreadParam) {
            $spreadParam->variadic = false;
            $spreadParam->type = new Identifier('array');
        }

        return $classMethod;
    }

    private function processUnspreadOperatorMethodCallArgs(MethodCall $methodCall): ?MethodCall
    {
        $classMethod = $this->nodeRepository->findClassMethodByMethodCall($methodCall);
        if (! $classMethod instanceof ClassMethod) {
            return null;
        }

        $spreadParams = $this->spreadVariablesCollector->resolveFromClassMethod($classMethod);
        if ($spreadParams === []) {
            return null;
        }

        $firstSpreadParamPosition = array_key_first($spreadParams);
        $variadicArgs = $this->resolveVariadicArgsByVariadicParams($methodCall, $firstSpreadParamPosition);

        $hasUnpacked = false;

        foreach ($variadicArgs as $position => $variadicArg) {
            if ($variadicArg->unpack) {
                $variadicArg->unpack = false;
                $hasUnpacked = true;
                $methodCall->args[$position] = $variadicArg;
            }
        }

        if ($hasUnpacked) {
            return $methodCall;
        }

        $methodCall->args[$firstSpreadParamPosition] = new Arg($this->nodeFactory->createArray($variadicArgs));
        return $methodCall;
    }

    /**
     * @return Arg[]
     */
    private function resolveVariadicArgsByVariadicParams(MethodCall $methodCall, int $firstSpreadParamPosition): array
    {
        $variadicArgs = [];

        foreach ($methodCall->args as $position => $arg) {
            if ($position < $firstSpreadParamPosition) {
                continue;
            }

            $variadicArgs[] = $arg;

            $this->nodeRemover->removeArg($methodCall, $position);
        }

        return $variadicArgs;
    }
}

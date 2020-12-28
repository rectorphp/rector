<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\UnSpreadOperatorRectorTest
 */
final class UnSpreadOperatorRectorTest extends AbstractRector
{
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
        return $node;
    }
}

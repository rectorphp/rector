<?php declare(strict_types=1);

namespace Rector\Laravel\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\LNumber;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/laravel/framework/pull/27276
 */
final class MinutesToSecondsInCacheRector extends AbstractRector
{
    /**
     * @var int[][]
     */
    private $typesToMethods = [
        'Illuminate\Support\Facades\Cache' => [
            'put' => 2, // time argument position
            'add' => 2,
        ],
        'Illuminate\Contracts\Cache\Store' => [
            'put' => 2,
            'putMany' => 1,
        ],
        'Illuminate\Cache\DynamoDbStore' => [
            'add' => 2,
        ],
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change minutes argument to seconds in Illuminate\Contracts\Cache\Store and Illuminate\Support\Facades\Cache',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        Illuminate\Support\Facades\Cache::put('key', 'value', 60);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        Illuminate\Support\Facades\Cache::put('key', 'value', 60 * 60);
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class, MethodCall::class];
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->typesToMethods as $type => $methodsToArguments) {
            if (! $this->isType($node, $type)) {
                continue;
            }

            foreach ($methodsToArguments as $method => $argumentPosition) {
                if (! $this->isName($node, $method)) {
                    continue;
                }

                if (! isset($node->args[$argumentPosition])) {
                    continue;
                }

                return $this->processArgumentPosition($node, $argumentPosition);
            }
        }

        return $node;
    }

    /**
     * @param StaticCall|MethodCall $expr
     * @return StaticCall|MethodCall
     */
    private function processArgumentPosition(Expr $expr, int $argumentPosition): Expr
    {
        $oldValue = $expr->args[$argumentPosition]->value;
        $newArgumentValue = new Mul($oldValue, new LNumber(60));

        $expr->args[$argumentPosition] = new Arg($newArgumentValue);

        return $expr;
    }
}

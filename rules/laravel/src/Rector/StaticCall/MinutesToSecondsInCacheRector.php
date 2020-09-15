<?php

declare(strict_types=1);

namespace Rector\Laravel\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\LNumber;
use PHPStan\Type\Constant\ConstantIntegerType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Laravel\ValueObject\TypeToTimeMethodAndPosition;

/**
 * @see https://github.com/laravel/framework/pull/27276
 * @see \Rector\Laravel\Tests\Rector\StaticCall\MinutesToSecondsInCacheRector\MinutesToSecondsInCacheRectorTest
 */
final class MinutesToSecondsInCacheRector extends AbstractRector
{
    /**
     * @var TypeToTimeMethodAndPosition[]
     */
    private $typeToTimeMethodsAndPositions = [];

    public function __construct()
    {
        $this->typeToTimeMethodsAndPositions = [
            new TypeToTimeMethodAndPosition('Illuminate\Support\Facades\Cache', 'put', 2),
            new TypeToTimeMethodAndPosition('Illuminate\Support\Facades\Cache', 'add', 2),
            new TypeToTimeMethodAndPosition('Illuminate\Contracts\Cache\Store', 'put', 2),
            new TypeToTimeMethodAndPosition('Illuminate\Contracts\Cache\Store', 'putMany', 1),
            new TypeToTimeMethodAndPosition('Illuminate\Cache\DynamoDbStore', 'add', 2),
        ];
    }

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
        foreach ($this->typeToTimeMethodsAndPositions as $typeToTimeMethodAndPosition) {
            if (! $this->isObjectType($node, $typeToTimeMethodAndPosition->getType())) {
                continue;
            }

            if (! $this->isName($node->name, $typeToTimeMethodAndPosition->getMethodName())) {
                continue;
            }

            if (! isset($node->args[$typeToTimeMethodAndPosition->getPosition()])) {
                continue;
            }

            return $this->processArgumentPosition($node, $typeToTimeMethodAndPosition->getPosition());
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
        if (! $oldValue instanceof LNumber && ! $this->getStaticType($oldValue) instanceof ConstantIntegerType) {
            return $expr;
        }

        $mul = new Mul($oldValue, new LNumber(60));

        $expr->args[$argumentPosition] = new Arg($mul);

        return $expr;
    }
}

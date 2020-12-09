<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\DowngradeTrailingCommasInParamUseRectorTest
 */
final class DowngradeTrailingCommasInParamUseRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove trailing commas in param or use list', [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(string $value1, string $value2,)
    {
        function (string $value1, string $value2,) {
        };

        function () use ($value1, $value2,) {
        };
    }
}

function inFunction(string $value1, string $value2,)
{
}
CODE_SAMPLE
                    , <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(string $value1, string $value2)
    {
        function (string $value1, string $value2) {
        };

        function () use ($value1, $value2) {
        };
    }
}

function inFunction(string $value1, string $value2)
{
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
        return [ClassMethod::class, Function_::class, Closure::class];
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Closure) {
            $node = $this->processUses($node);
        }

        return $this->processParams($node);
    }

    private function processParams(Node $node): ?Node
    {
        if ($node->params === []) {
            return null;
        }

        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $last = $node->params[array_key_last($node->params)];
        $last->setAttribute(AttributeKey::FUNC_ARGS_TRAILING_COMMA, false);

        return $node;
    }

    private function processUses(Closure $node): Closure
    {
        if ($node->uses === []) {
            return $node;
        }

        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $last = $node->uses[array_key_last($node->uses)];
        $last->setAttribute(AttributeKey::FUNC_ARGS_TRAILING_COMMA, false);

        return $node;
    }
}

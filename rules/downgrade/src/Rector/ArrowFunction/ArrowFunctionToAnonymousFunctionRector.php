<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\ArrowFunction;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType;
use Rector\AbstractRector\Rector\AbstractConvertToAnonymousFunctionRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://www.php.net/manual/en/functions.arrow.php
 *
 * @see \Rector\Downgrade\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\ArrowFunctionToAnonymousFunctionRectorTest
 */
final class ArrowFunctionToAnonymousFunctionRector extends AbstractConvertToAnonymousFunctionRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace arrow functions with anonymous functions', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $delimiter = ",";
        $callable = fn($matches) => $delimiter . strtolower($matches[1]);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $delimiter = ",";
        $callable = function ($matches) use ($delimiter) {
            return $delimiter . strtolower($matches[1]);
        };
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ArrowFunction::class];
    }

    /**
     * @param ArrowFunction $node
     */
    protected function shouldSkip(Node $node): bool
    {
        return false;
    }

    /**
     * @param ArrowFunction $node
     * @return Param[]
     */
    protected function getParameters(Node $node): array
    {
        return $node->params;
    }

    /**
     * @param ArrowFunction $node
     * @return Identifier|Name|NullableType|UnionType|null
     */
    protected function getReturnType(Node $node): ?Node
    {
        return $node->returnType;
    }

    /**
     * @param ArrowFunction $node
     * @return Return_[]
     */
    protected function getBody(Node $node): array
    {
        return [new Return_($node->expr)];
    }
}

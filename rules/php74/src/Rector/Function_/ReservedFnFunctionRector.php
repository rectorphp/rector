<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/php/php-src/pull/3941/files#diff-7e3a1a5df28a1cbd8c0fb6db68f243da
 * @see \Rector\Php74\Tests\Rector\Function_\ReservedFnFunctionRector\ReservedFnFunctionRectorTest
 */
final class ReservedFnFunctionRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $reservedNamesToNewOnes = [];

    public function __construct(array $reservedNamesToNewOnes = [
        // PHP 7.4
        'fn' => 'f',
    ])
    {
        $this->reservedNamesToNewOnes = $reservedNamesToNewOnes;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change fn() function name, since it will be reserved keyword', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        function fn($value)
        {
            return $value;
        }

        fn(5);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        function f($value)
        {
            return $value;
        }

        f(5);
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
        return [Function_::class, FuncCall::class];
    }

    /**
     * @param Function_|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->reservedNamesToNewOnes as $reservedName => $newName) {
            if (! $this->isName($node->name, $reservedName)) {
                continue;
            }

            if ($node instanceof FuncCall) {
                $node->name = new Name($newName);
            } else {
                $node->name = new Identifier($newName);
            }

            return $node;
        }

        return null;
    }
}

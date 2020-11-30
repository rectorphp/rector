<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/php/php-src/pull/3941/files#diff-7e3a1a5df28a1cbd8c0fb6db68f243da
 * @see \Rector\Php74\Tests\Rector\Function_\ReservedFnFunctionRector\ReservedFnFunctionRectorTest
 */
final class ReservedFnFunctionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const RESERVED_NAMES_TO_NEW_ONES = '$reservedNamesToNewOnes';

    /**
     * @var string[]
     */
    private $reservedNamesToNewOnes = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change fn() function name, since it will be reserved keyword', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                ,
                [
                    self::RESERVED_NAMES_TO_NEW_ONES => [
                        'fn' => 'someFunctionName',
                    ],
                ]
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

    public function configure(array $configuration): void
    {
        $this->reservedNamesToNewOnes = $configuration[self::RESERVED_NAMES_TO_NEW_ONES] ?? [];
    }
}

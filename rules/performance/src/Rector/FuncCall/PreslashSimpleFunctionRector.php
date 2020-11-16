<?php

declare(strict_types=1);

namespace Rector\Performance\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;

/**
 * @see https://stackoverflow.com/questions/55419673/php7-adding-a-slash-to-all-standard-php-functions-php-cs-fixer-rule
 *
 * @see \Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\PreslashSimpleFunctionRectorTest
 */
final class PreslashSimpleFunctionRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(
            'Add pre-slash to short named functions to improve performance',
            [
                new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function shorten($value)
    {
        return trim($value);
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function shorten($value)
    {
        return \trim($value);
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->name instanceof FullyQualified) {
            return null;
        }

        $functionName = $this->getName($node);
        if ($functionName === null) {
            return null;
        }

        if (Strings::contains($functionName, '\\')) {
            return null;
        }

        $node->name = new FullyQualified($functionName);

        return $node;
    }
}

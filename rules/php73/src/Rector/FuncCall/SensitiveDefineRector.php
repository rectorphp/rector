<?php

declare(strict_types=1);

namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;

/**
 * @see https://wiki.php.net/rfc/case_insensitive_constant_deprecation
 * @see \Rector\Php73\Tests\Rector\FuncCall\SensitiveDefineRector\SensitiveDefineRectorTest
 */
final class SensitiveDefineRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(
            'Changes case insensitive constants to sensitive ones.',
            [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
define('FOO', 42, true);
CODE_SAMPLE
                , <<<'CODE_SAMPLE'
define('FOO', 42);
CODE_SAMPLE
            )]
        );
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
        if (! $this->isName($node, 'define')) {
            return null;
        }

        if (! isset($node->args[2])) {
            return null;
        }

        unset($node->args[2]);

        return $node;
    }
}

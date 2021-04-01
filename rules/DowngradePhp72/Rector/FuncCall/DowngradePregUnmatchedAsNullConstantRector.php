<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\DowngradePregUnmatchedAsNullConstantRectorTest
 */
final class DowngradePregUnmatchedAsNullConstantRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const REGEX_FUNCTION_NAMES = [
        'preg_match',
        'preg_match_all',
    ];

    /**
     * @return array<class-string<Node>>
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
        if ($this->isNames($node, self::REGEX_FUNCTION_NAMES)) {
            return null;
        }

        $args = $node->args;
        if (! isset($args[3])) {
            return null;
        }

        $flags = $args[3]->value;
        dump($flags);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove PREG_UNMATCHED_AS_NULL from preg_match and set null value on empty string matched on each match',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, PREG_UNMATCHED_AS_NULL);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches);
        array_walk_recursive($matches, function (& $value) {
            if ($value === '') {
                $value = null;
            }
        });
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }
}

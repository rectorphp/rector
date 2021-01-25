<?php

declare(strict_types=1);

namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://www.tomasvotruba.cz/blog/2018/08/16/whats-new-in-php-73-in-30-seconds-in-diffs/#2-first-and-last-array-key
 *
 * This needs to removed 1 floor above, because only nodes in arrays can be removed why traversing,
 * see https://github.com/nikic/PHP-Parser/issues/389
 * @see \Rector\Php73\Tests\Rector\FuncCall\ArrayKeyFirstLastRector\ArrayKeyFirstLastRectorTest
 */
final class ArrayKeyFirstLastRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ARRAY_KEY_FIRST = 'array_key_first';

    /**
     * @var string
     */
    private const ARRAY_KEY_LAST = 'array_key_last';

    /**
     * @var array<string, string>
     */
    private const PREVIOUS_TO_NEW_FUNCTIONS = [
        'reset' => self::ARRAY_KEY_FIRST,
        'end' => self::ARRAY_KEY_LAST,
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Make use of array_key_first() and array_key_last()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
reset($items);
$firstKey = key($items);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$firstKey = array_key_first($items);
CODE_SAMPLE
                ),
                new CodeSample(
<<<'CODE_SAMPLE'
end($items);
$lastKey = key($items);
CODE_SAMPLE
                    ,
<<<'CODE_SAMPLE'
$lastKey = array_key_last($items);
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $nextExpression = $this->getNextExpression($node);
        if (! $nextExpression instanceof Node) {
            return null;
        }

        $resetOrEndFuncCall = $node;

        $keyFuncCall = $this->betterNodeFinder->findFirst($nextExpression, function (Node $node) use (
            $resetOrEndFuncCall
        ): bool {
            if (! $node instanceof FuncCall) {
                return false;
            }

            if (! $this->isName($node, 'key')) {
                return false;
            }

            return $this->areNodesEqual($resetOrEndFuncCall->args[0], $node->args[0]);
        });

        if (! $keyFuncCall instanceof FuncCall) {
            return null;
        }

        $newName = self::PREVIOUS_TO_NEW_FUNCTIONS[$this->getName($node)];
        $keyFuncCall->name = new Name($newName);

        $this->removeNode($node);

        return $node;
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->isNames($funcCall, ['reset', 'end'])) {
            return true;
        }

        if ($this->isAtLeastPhpVersion(PhpVersionFeature::ARRAY_KEY_FIRST_LAST)) {
            return false;
        }
        return ! (function_exists(self::ARRAY_KEY_FIRST) && function_exists(self::ARRAY_KEY_LAST));
    }
}

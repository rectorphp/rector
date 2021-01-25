<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\LNumber;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/numeric_literal_separator
 * @see \Rector\DowngradePhp74\Tests\Rector\LNumber\DowngradeNumericLiteralSeparatorRector\DowngradeNumericLiteralSeparatorRectorTest
 */
final class DowngradeNumericLiteralSeparatorRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove "_" as thousands separator in numbers',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $int = 1_000;
        $float = 1_000_500.001;
    }
}
CODE_SAMPLE
,
<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $int = 1000;
        $float = 1000500.001;
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
        return [LNumber::class, DNumber::class];
    }

    /**
     * @param LNumber|DNumber $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->shouldRefactor($node)) {
            return null;
        }

        $numberNode = clone $node;
        $numberNodeValue = (string) $numberNode->value;
        if (Strings::contains($numberNodeValue, '+')) {
            return null;
        }

        $node->value = (string) $node->value;

        /**
         * This code follows a guess, to avoid modifying floats needlessly.
         * If the node is a float, but it doesn't contain ".",
         * then it's likely that the number was forced to be a float
         * by adding ".0" at the end (eg: 0.0).
         * Then, add it again.
         */
        if ($node instanceof DNumber && ! Strings::contains($node->value, '.')) {
            $node->value .= '.0';
        }
        return $node;
    }

    /**
     * @param LNumber|DNumber $node
     */
    public function shouldRefactor(Node $node): bool
    {
        // "_" notation can be applied to decimal numbers only
        if ($node instanceof LNumber) {
            return $node->getAttribute(AttributeKey::KIND) === LNumber::KIND_DEC;
        }
        return true;
    }
}

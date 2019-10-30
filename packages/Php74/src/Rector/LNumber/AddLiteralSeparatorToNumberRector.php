<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\LNumber;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/numeric_literal_separator
 * @see https://github.com/nikic/PHP-Parser/pull/615
 * @see \Rector\Php74\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector\AddLiteralSeparatorToNumberRectorTest
 *
 * Taking the most generic use case to the account: https://wiki.php.net/rfc/numeric_literal_separator#should_it_be_the_role_of_an_ide_to_group_digits
 * The final check should be done manually
 */
final class AddLiteralSeparatorToNumberRector extends AbstractRector
{
    /**
     * @var int
     */
    private const GROUP_SIZE = 3;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add "_" as thousands separator in numbers', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $int = 1000;
        $float = 1000500.001;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $int = 1_000;
        $float = 1_000_500.001;
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
        return [LNumber::class, DNumber::class];
    }

    /**
     * @param LNumber|DNumber $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion('7.4')) {
            return null;
        }

        $numericValueAsString = (string) $node->value;
        if ($this->shouldSkip($node, $numericValueAsString)) {
            return null;
        }

        if (Strings::contains($numericValueAsString, '.')) {
            [$mainPart, $decimalPart] = explode('.', $numericValueAsString);

            $chunks = $this->strSplitNegative($mainPart, self::GROUP_SIZE);
            $literalSeparatedNumber = implode('_', $chunks) . '.' . $decimalPart;
        } else {
            $chunks = $this->strSplitNegative($numericValueAsString, self::GROUP_SIZE);
            $literalSeparatedNumber = implode('_', $chunks);
        }

        $node->value = $literalSeparatedNumber;

        return $node;
    }

    /**
     * @param LNumber|DNumber $node
     */
    private function shouldSkip(Node $node, string $numericValueAsString): bool
    {
        // already separated
        if (Strings::contains($numericValueAsString, '_')) {
            return true;
        }

        $kind = $node->getAttribute('kind');
        if (in_array($kind, [LNumber::KIND_BIN, LNumber::KIND_OCT, LNumber::KIND_HEX], true)) {
            return true;
        }

        // e+/e-
        if (Strings::match($numericValueAsString, '#e#i')) {
            return true;
        }
        // too short
        return Strings::length($numericValueAsString) <= self::GROUP_SIZE;
    }

    /**
     * @return string[]
     */
    private function strSplitNegative(string $string, int $length): array
    {
        $inversed = strrev($string);

        /** @var string[] $chunks */
        $chunks = str_split($inversed, $length);

        $chunks = array_reverse($chunks);
        foreach ($chunks as $key => $chunk) {
            $chunks[$key] = strrev($chunk);
        }

        return $chunks;
    }
}

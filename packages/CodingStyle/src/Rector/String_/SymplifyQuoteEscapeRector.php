<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\String_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\String_\SymplifyQuoteEscapeRector\SymplifyQuoteEscapeRectorTest
 */
final class SymplifyQuoteEscapeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Prefer quote that are not inside the string', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
         $name = "\" Tom";
         $name = '\' Sara';
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
         $name = '" Tom';
         $name = "' Sara";
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
        return [String_::class];
    }

    /**
     * @param String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $doubleQuoteCount = substr_count($node->value, '"');
        $singleQuoteCount = substr_count($node->value, "'");

        if ($node->getAttribute(AttributeKey::KIND) === String_::KIND_SINGLE_QUOTED) {
            $this->processSingleQuoted($node, $doubleQuoteCount, $singleQuoteCount);
        }

        if ($node->getAttribute(AttributeKey::KIND) === String_::KIND_DOUBLE_QUOTED) {
            $this->processDoubleQuoted($node, $singleQuoteCount, $doubleQuoteCount);
        }

        return $node;
    }

    private function processSingleQuoted(String_ $string, int $doubleQuoteCount, int $singleQuoteCount): void
    {
        if ($doubleQuoteCount === 0 && $singleQuoteCount > 0) {
            // contains chars tha will be newly escaped
            $matches = Strings::match($string->value, '#\\\\|\$#sim');
            if ($matches) {
                return;
            }

            $string->setAttribute(AttributeKey::KIND, String_::KIND_DOUBLE_QUOTED);
            // invoke override
            $string->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
    }

    private function processDoubleQuoted(String_ $string, int $singleQuoteCount, int $doubleQuoteCount): void
    {
        if ($singleQuoteCount === 0 && $doubleQuoteCount > 0) {
            $string->setAttribute(AttributeKey::KIND, String_::KIND_SINGLE_QUOTED);
            // invoke override
            $string->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
    }
}

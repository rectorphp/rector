<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\String_;

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
        return new RectorDefinition('Prefer quote that not inside the string', [
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
            if ($doubleQuoteCount === 0 && $singleQuoteCount > 0) {
                $node->setAttribute(AttributeKey::KIND, String_::KIND_DOUBLE_QUOTED);
                // invoke override
                $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            }
        }

        if ($node->getAttribute(AttributeKey::KIND) === String_::KIND_DOUBLE_QUOTED) {
            if ($singleQuoteCount === 0 && $doubleQuoteCount > 0) {
                $node->setAttribute(AttributeKey::KIND, String_::KIND_SINGLE_QUOTED);
                // invoke override
                $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            }
        }

        return $node;
    }
}

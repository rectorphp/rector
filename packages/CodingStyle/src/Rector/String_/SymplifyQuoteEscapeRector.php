<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SymplifyQuoteEscapeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Prefer quote that not inside the string', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
         $name = "\" Tom";
         $name = '\' Sara';
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
         $name = '" Tom';
         $name = "' Sara";
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
        return [String_::class];
    }

    /**
     * @param String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $doubleQuoteCount = substr_count($node->value, '"');
        $singleQuoteCount = substr_count($node->value, "'");

        if ($node->getAttribute(Attribute::KIND) === String_::KIND_SINGLE_QUOTED) {
            if ($doubleQuoteCount === 0 && $singleQuoteCount > 0) {
                $node->setAttribute(Attribute::KIND, String_::KIND_DOUBLE_QUOTED);
                // invoke override
                $node->setAttribute(Attribute::ORIGINAL_NODE, null);
            }
        }

        if ($node->getAttribute(Attribute::KIND) === String_::KIND_DOUBLE_QUOTED) {
            if ($singleQuoteCount === 0 && $doubleQuoteCount > 0) {
                $node->setAttribute(Attribute::KIND, String_::KIND_SINGLE_QUOTED);
                // invoke override
                $node->setAttribute(Attribute::ORIGINAL_NODE, null);
            }
        }

        return $node;
    }
}

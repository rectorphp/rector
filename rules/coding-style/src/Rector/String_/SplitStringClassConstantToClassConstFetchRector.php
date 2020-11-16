<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;

/**
 * @see \Rector\CodingStyle\Tests\Rector\String_\SplitStringClassConstantToClassConstFetchRector\SplitStringClassConstantToClassConstFetchRectorTest
 */
final class SplitStringClassConstantToClassConstFetchRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(
            'Separate class constant in a string to class constant fetch and string',
            [
                new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    const HI = true;
}

class AnotherClass
{
    public function get()
    {
        return 'SomeClass::HI';
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    const HI = true;
}

class AnotherClass
{
    public function get()
    {
        return SomeClass::class . '::HI';
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
        if (substr_count($node->value, '::') !== 1) {
            return null;
        }

        // a possible constant reference
        [$possibleClass, $secondPart] = explode('::', $node->value);

        if (! class_exists($possibleClass)) {
            return null;
        }

        $classConstFetch = new ClassConstFetch(new FullyQualified(ltrim($possibleClass, '\\')), 'class');

        return new Concat($classConstFetch, new String_('::' . $secondPart));
    }
}

<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\String_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SplitStringClassConstantToClassConstFetchRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Separate class constant in a string to class constant fetch and string', [
            new CodeSample(
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
        [$possibleClass, $secondPart] = Strings::split($node->value, '#::#');

        if (! class_exists($possibleClass)) {
            return null;
        }

        $classConstFetch = new Node\Expr\ClassConstFetch(new Node\Name\FullyQualified($possibleClass), 'class');

        return new Node\Expr\BinaryOp\Concat($classConstFetch, new Node\Scalar\String_('::' . $secondPart));
    }
}

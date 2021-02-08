<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/stringable
 *
 * @see \Rector\Php80\Tests\Rector\Class_\StringableForToStringRector\StringableForToStringRectorTest
 */
final class StringableForToStringRector extends AbstractRector
{
    /**
     * @var string
     */
    private const STRINGABLE = 'Stringable';

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(ClassManipulator $classManipulator)
    {
        $this->classManipulator = $classManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add `Stringable` interface to classes with `__toString()` method',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function __toString()
    {
        return 'I can stringz';
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass implements Stringable
{
    public function __toString(): string
    {
        return 'I can stringz';
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $toStringClassMethod = $node->getMethod('__toString');
        if (! $toStringClassMethod instanceof ClassMethod) {
            return null;
        }

        if ($this->classManipulator->hasInterface($node, self::STRINGABLE)) {
            return null;
        }

        // add interface
        $node->implements[] = new FullyQualified(self::STRINGABLE);

        // add return type

        if ($toStringClassMethod->returnType === null) {
            $toStringClassMethod->returnType = new Name('string');
        }

        return $node;
    }
}

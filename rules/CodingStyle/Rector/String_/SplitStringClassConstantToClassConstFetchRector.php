<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector\SplitStringClassConstantToClassConstFetchRectorTest
 */
final class SplitStringClassConstantToClassConstFetchRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Separate class constant in a string to class constant fetch and string', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Scalar\String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (\substr_count($node->value, '::') !== 1) {
            return null;
        }
        // a possible constant reference
        [$possibleClass, $secondPart] = \explode('::', $node->value);
        if (!$this->reflectionProvider->hasClass($possibleClass)) {
            return null;
        }
        $classConstFetch = new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name\FullyQualified(\ltrim($possibleClass, '\\')), 'class');
        return new \PhpParser\Node\Expr\BinaryOp\Concat($classConstFetch, new \PhpParser\Node\Scalar\String_('::' . $secondPart));
    }
}

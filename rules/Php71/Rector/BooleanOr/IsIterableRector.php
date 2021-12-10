<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\BooleanOr;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Name;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php71\IsArrayAndDualCheckToAble;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php71\Rector\BooleanOr\IsIterableRector\IsIterableRectorTest
 */
final class IsIterableRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php71\IsArrayAndDualCheckToAble
     */
    private $isArrayAndDualCheckToAble;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\Php71\IsArrayAndDualCheckToAble $isArrayAndDualCheckToAble, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->isArrayAndDualCheckToAble = $isArrayAndDualCheckToAble;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::IS_ITERABLE;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes is_array + Traversable check to is_iterable', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('is_array($foo) || $foo instanceof Traversable;', 'is_iterable($foo);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\BinaryOp\BooleanOr::class];
    }
    /**
     * @param BooleanOr $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip()) {
            return null;
        }
        return $this->isArrayAndDualCheckToAble->processBooleanOr($node, 'Traversable', 'is_iterable');
    }
    private function shouldSkip() : bool
    {
        if ($this->reflectionProvider->hasFunction(new \PhpParser\Node\Name('is_iterable'), null)) {
            return \false;
        }
        return !$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::IS_ITERABLE);
    }
}

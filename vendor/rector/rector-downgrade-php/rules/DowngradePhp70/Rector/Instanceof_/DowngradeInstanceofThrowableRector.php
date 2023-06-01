<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\Instanceof_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeTraverser;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Throwable was introduced in PHP 7.0 so to support older versions we need to also check for Exception.
 *
 * @changelog https://www.php.net/manual/en/class.throwable.php
 * @see \Rector\Tests\DowngradePhp70\Rector\Instanceof_\DowngradeInstanceofThrowableRector\DowngradeInstanceofThrowableRectorTest
 */
final class DowngradeInstanceofThrowableRector extends AbstractRector
{
    /**
     * @var string
     */
    private const JUST_CREATED_ATTRIBUTE = 'just_created';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add `instanceof Exception` check as a fallback to `instanceof Throwable` to support exception hierarchies in PHP 5', [new CodeSample(<<<'CODE_SAMPLE'
return $exception instanceof \Throwable;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return $exception instanceof \Throwable || $exception instanceof \Exception;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Instanceof_::class, BooleanOr::class];
    }
    /**
     * @param Instanceof_|BooleanOr $node
     */
    public function refactor(Node $node)
    {
        if ($node instanceof BooleanOr) {
            if ($this->isInstanceofNamed($node->left, 'Throwable') && $this->isInstanceofNamed($node->right, 'Exception')) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            return null;
        }
        if ($node->getAttribute(self::JUST_CREATED_ATTRIBUTE)) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($node->class, 'Throwable')) {
            return null;
        }
        $throwableInstanceof = new Instanceof_($node->expr, new FullyQualified('Throwable'));
        $throwableInstanceof->setAttribute(self::JUST_CREATED_ATTRIBUTE, \true);
        $exceptionInstanceof = new Instanceof_($node->expr, new FullyQualified('Exception'));
        return new BooleanOr($throwableInstanceof, $exceptionInstanceof);
    }
    private function isInstanceofNamed(Expr $expr, string $className) : bool
    {
        if (!$expr instanceof Instanceof_) {
            return \false;
        }
        return $this->nodeNameResolver->isName($expr->class, $className);
    }
}

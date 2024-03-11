<?php

declare (strict_types=1);
namespace Rector\Php82\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php82\Rector\New_\FilesystemIteratorSkipDotsRector\FilesystemIteratorSkipDotsRectorTest
 */
final class FilesystemIteratorSkipDotsRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Prior PHP 8.2 FilesystemIterator::SKIP_DOTS was always set and could not be removed, therefore FilesystemIterator::SKIP_DOTS is added in order to keep this behaviour.', [new CodeSample('new FilesystemIterator(__DIR__, FilesystemIterator::KEY_AS_FILENAME);', 'new FilesystemIterator(__DIR__, FilesystemIterator::KEY_AS_FILENAME | FilesystemIterator::SKIP_DOTS);')]);
    }
    public function getNodeTypes() : array
    {
        return [New_::class];
    }
    /**
     * Add {@see \FilesystemIterator::SKIP_DOTS} to $node when required.
     *
     * @param New_ $node
     */
    public function refactor(Node $node) : ?New_
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isObjectType($node->class, new ObjectType('FilesystemIterator'))) {
            return null;
        }
        if (!isset($node->args[1])) {
            return null;
        }
        $flags = $node->getArgs()[1]->value;
        if ($this->isSkipDotsPresent($flags)) {
            return null;
        }
        $classConstFetch = new ClassConstFetch(new FullyQualified('FilesystemIterator'), 'SKIP_DOTS');
        $node->args[1] = new Arg(new BitwiseOr($flags, $classConstFetch));
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::FILESYSTEM_ITERATOR_SKIP_DOTS;
    }
    /**
     * Is the constant {@see \FilesystemIterator::SKIP_DOTS} present within $node?
     */
    private function isSkipDotsPresent(Expr $expr) : bool
    {
        while ($expr instanceof BitwiseOr) {
            if ($this->isSkipDots($expr->right)) {
                return \true;
            }
            $expr = $expr->left;
        }
        return $this->isSkipDots($expr);
    }
    /**
     * Tells if $expr is equal to {@see \FilesystemIterator::SKIP_DOTS}.
     */
    private function isSkipDots(Expr $expr) : bool
    {
        if (!$expr instanceof ClassConstFetch) {
            // can be anything
            return \true;
        }
        if (!\defined('FilesystemIterator::SKIP_DOTS')) {
            return \true;
        }
        $value = \constant('FilesystemIterator::SKIP_DOTS');
        return $this->valueResolver->isValue($expr, $value);
    }
}

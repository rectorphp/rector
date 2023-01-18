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
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeNameResolver\NodeNameResolver\ClassConstFetchNameResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php82\Rector\New_\FilesystemIteratorSkipDots\FilesystemIteratorSkipDotsRectorTest
 */
class FilesystemIteratorSkipDotsRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver\ClassConstFetchNameResolver
     */
    private $classConstFetchNameResolver;
    public function __construct(ClassConstFetchNameResolver $classConstFetchNameResolver)
    {
        $this->classConstFetchNameResolver = $classConstFetchNameResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Prior PHP 8.2 FilesystemIterator::SKIP_DOTS was always set and could not be removed, therefore FilesystemIterator::SKIP_DOTS is added in order to keep this behaviour.', [new CodeSample('new \\FilesystemIterator(__DIR__, \\FilesystemIterator::KEY_AS_FILENAME);', 'new \\FilesystemIterator(__DIR__, \\FilesystemIterator::KEY_AS_FILENAME | \\FilesystemIterator::SKIP_DOTS);')]);
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
        if (!\array_key_exists(1, $node->args)) {
            return null;
        }
        $flags = $node->args[1]->value;
        if ($this->isSkipDotsPresent($flags)) {
            return null;
        }
        $skipDots = new ClassConstFetch(new FullyQualified('FilesystemIterator'), 'SKIP_DOTS');
        $node->args[1] = new Arg(new BitwiseOr($flags, $skipDots));
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::FILESYSTEM_ITERATOR_SKIP_DOTS;
    }
    /**
     * Is the constant {@see \FilesystemIterator::SKIP_DOTS} present within $node?
     */
    private function isSkipDotsPresent(Expr $node) : bool
    {
        while ($node instanceof BitwiseOr) {
            if ($this->isSkipDots($node->right)) {
                return \true;
            }
            $node = $node->left;
        }
        return $this->isSkipDots($node);
    }
    /**
     * Tells if $expr is equal to {@see \FilesystemIterator::SKIP_DOTS}.
     */
    private function isSkipDots(Expr $expr) : bool
    {
        if (!$expr instanceof ClassConstFetch) {
            return \false;
        }
        return $this->classConstFetchNameResolver->resolve($expr) === 'FilesystemIterator::SKIP_DOTS';
    }
}

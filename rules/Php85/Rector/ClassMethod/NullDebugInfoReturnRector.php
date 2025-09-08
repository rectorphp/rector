<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitor;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_debuginfo_returning_null
 * @see \Rector\Tests\Php85\Rector\MethodCall\NullDebugInfoReturnRector\NullDebugInfoReturnRectorTest
 */
final class NullDebugInfoReturnRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces `null` return value with empty array in `__debugInfo` methods', [new CodeSample(<<<'CODE_SAMPLE'
new class
{
    public function __debugInfo() {
        return null;
    }
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
new class
{
    public function __debugInfo() {
        return [];
    }
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, '__debugInfo')) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable((array) $node->stmts, function (Node $node) use (&$hasChanged) {
            if ($node instanceof Class_ || $node instanceof Function_ || $node instanceof Closure) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($node instanceof Return_ && (!$node->expr instanceof Expr || $this->valueResolver->isNull($node->expr))) {
                $hasChanged = \true;
                $node->expr = new Array_();
                return $node;
            }
            return null;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATED_NULL_DEBUG_INFO_RETURN;
    }
}

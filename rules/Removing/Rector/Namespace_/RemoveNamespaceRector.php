<?php

declare (strict_types=1);
namespace Rector\Removing\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220418\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Removing\Rector\Namespace_\RemoveNamespaceRector\RemoveNamespaceRectorTest
 */
final class RemoveNamespaceRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private $removeNamespaces = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove namespace by configured namespace names', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
namespace App;
class SomeClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
}
CODE_SAMPLE
, ['App'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Namespace_::class];
    }
    /**
     * @param Namespace_ $node
     * @return mixed[]|\PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        $namespaceName = $this->nodeNameResolver->getName($node);
        if ($namespaceName === null) {
            return null;
        }
        foreach ($this->removeNamespaces as $removeNamespace) {
            if ($removeNamespace !== $namespaceName) {
                continue;
            }
            return $this->processRemoveNamespace($node);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220418\Webmozart\Assert\Assert::allString($configuration);
        $this->removeNamespaces = $configuration;
    }
    /**
     * @return mixed[]|\PhpParser\Node\Stmt\Namespace_
     */
    private function processRemoveNamespace(\PhpParser\Node\Stmt\Namespace_ $namespace)
    {
        $stmts = $this->cleanNonCompoundUseName($namespace->stmts);
        // has prev or next namespace should just clean namespace name to avoid error
        // `Namespace declaration statement has to be the very first statement` ref https://3v4l.org/qUMfb
        // or `No code may exist outside of namespace {}` ref https://3v4l.org/ct7SR
        if ($this->hasMultipleNamespaces($namespace)) {
            return new \PhpParser\Node\Stmt\Namespace_(null, $stmts);
        }
        if ($stmts === []) {
            $this->removeNode($namespace);
            return $namespace;
        }
        return $stmts;
    }
    private function hasMultipleNamespaces(\PhpParser\Node\Stmt\Namespace_ $namespace) : bool
    {
        $prev = $namespace->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT);
        $next = $namespace->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        return $prev instanceof \PhpParser\Node\Stmt\Namespace_ || $next instanceof \PhpParser\Node\Stmt\Namespace_;
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function cleanNonCompoundUseName(array $stmts) : array
    {
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Use_) {
                continue;
            }
            $uses = $stmt->uses;
            foreach ($uses as $keyUse => $use) {
                if ($use->alias instanceof \PhpParser\Node\Identifier) {
                    continue;
                }
                $useName = \ltrim($use->name->toString(), '\\');
                if (\strpos($useName, '\\') === \false) {
                    unset($uses[$keyUse]);
                }
            }
            if ($uses === []) {
                unset($stmts[$key]);
                continue;
            }
            $uses = \array_values($uses);
            $stmt->uses = $uses;
        }
        return $stmts;
    }
}

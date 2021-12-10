<?php

declare (strict_types=1);
namespace Rector\DowngradePhp56\Rector\Use_;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/use_function
 *
 * @see \Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\DowngradeUseFunctionRectorTest
 */
final class DowngradeUseFunctionRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace imports of functions and constants', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use function Foo\Bar\baz;

$var = baz();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$var = \Foo\Bar\baz();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Use_::class, \PhpParser\Node\Expr\ConstFetch::class, \PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param Use_|ConstFetch|FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\Use_) {
            $this->refactorUse($node);
            return null;
        }
        if ($this->isAlreadyFullyQualified($node)) {
            return null;
        }
        $name = $this->getFullyQualifiedName($node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES), $node);
        if ($name === null) {
            return null;
        }
        $node->name = new \PhpParser\Node\Name\FullyQualified($name);
        return $node;
    }
    private function refactorUse(\PhpParser\Node\Stmt\Use_ $use) : void
    {
        if ($use->type === \PhpParser\Node\Stmt\Use_::TYPE_FUNCTION || $use->type === \PhpParser\Node\Stmt\Use_::TYPE_CONSTANT) {
            $this->removeNode($use);
        }
    }
    /**
     * @param \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\FuncCall $node
     */
    private function isAlreadyFullyQualified($node) : bool
    {
        $oldTokens = $this->file->getOldTokens();
        $startTokenPos = $node->getStartTokenPos();
        $name = $oldTokens[$startTokenPos][1] ?? null;
        if (!\is_string($name)) {
            return \false;
        }
        return \strncmp($name, '\\', \strlen('\\')) === 0;
    }
    /**
     * @param Use_[] $useNodes
     * @param \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\FuncCall $node
     */
    private function getFullyQualifiedName(array $useNodes, $node) : ?string
    {
        if (!$node->name instanceof \PhpParser\Node\Name) {
            return null;
        }
        $name = $node->name->toLowerString();
        $typeFilter = $node instanceof \PhpParser\Node\Expr\ConstFetch ? \PhpParser\Node\Stmt\Use_::TYPE_CONSTANT : \PhpParser\Node\Stmt\Use_::TYPE_FUNCTION;
        foreach ($useNodes as $useNode) {
            if ($useNode->type !== $typeFilter) {
                continue;
            }
            foreach ($useNode->uses as $useUse) {
                if ($name === $useUse->name->toLowerString()) {
                    return $useUse->name->toString();
                }
                $alias = $useUse->getAlias();
                if ($name === $alias->toLowerString()) {
                    return $useUse->name->toString();
                }
            }
        }
        return null;
    }
}

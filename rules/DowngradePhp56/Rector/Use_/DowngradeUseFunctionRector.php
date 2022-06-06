<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp56\Rector\Use_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Stmt\GroupUse;
use RectorPrefix20220606\PhpParser\Node\Stmt\Use_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Naming\Naming\UseImportsResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/use_function
 *
 * @see \Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\DowngradeUseFunctionRectorTest
 */
final class DowngradeUseFunctionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    public function __construct(UseImportsResolver $useImportsResolver)
    {
        $this->useImportsResolver = $useImportsResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace imports of functions and constants', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Use_::class, ConstFetch::class, FuncCall::class];
    }
    /**
     * @param Use_|ConstFetch|FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Use_) {
            $this->refactorUse($node);
            return null;
        }
        if ($this->isAlreadyFullyQualified($node)) {
            return null;
        }
        $uses = $this->useImportsResolver->resolveForNode($node);
        $name = $this->getFullyQualifiedName($uses, $node);
        if ($name === null) {
            return null;
        }
        $node->name = new FullyQualified($name);
        return $node;
    }
    private function refactorUse(Use_ $use) : void
    {
        if ($use->type === Use_::TYPE_FUNCTION || $use->type === Use_::TYPE_CONSTANT) {
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
     * @param Use_[]|GroupUse[] $useNodes
     * @param \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\FuncCall $node
     */
    private function getFullyQualifiedName(array $useNodes, $node) : ?string
    {
        if (!$node->name instanceof Name) {
            return null;
        }
        $name = $node->name->toLowerString();
        $typeFilter = $node instanceof ConstFetch ? Use_::TYPE_CONSTANT : Use_::TYPE_FUNCTION;
        foreach ($useNodes as $useNode) {
            $prefix = $this->resolvePrefix($useNode);
            if ($useNode->type !== $typeFilter) {
                continue;
            }
            foreach ($useNode->uses as $useUse) {
                if ($name === $prefix . $useUse->name->toLowerString()) {
                    return $prefix . $useUse->name->toString();
                }
                $alias = $useUse->getAlias();
                if ($name === $alias->toLowerString()) {
                    return $prefix . $useUse->name->toString();
                }
            }
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\Use_|\PhpParser\Node\Stmt\GroupUse $useNode
     */
    private function resolvePrefix($useNode) : string
    {
        return $useNode instanceof GroupUse ? $useNode->prefix . '\\' : '';
    }
}

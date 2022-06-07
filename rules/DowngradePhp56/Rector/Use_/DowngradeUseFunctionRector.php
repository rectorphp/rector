<?php

declare(strict_types=1);

namespace Rector\DowngradePhp56\Rector\Use_;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\UseImportsResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/use_function
 *
 * @see \Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\DowngradeUseFunctionRectorTest
 */
final class DowngradeUseFunctionRector extends AbstractRector
{
    public function __construct(
        private readonly UseImportsResolver $useImportsResolver,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace imports of functions and constants',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use function Foo\Bar\baz;

$var = baz();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$var = \Foo\Bar\baz();
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Use_::class, ConstFetch::class, FuncCall::class];
    }

    /**
     * @param Use_|ConstFetch|FuncCall $node
     */
    public function refactor(Node $node): ?Node
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

    private function refactorUse(Use_ $use): void
    {
        if ($use->type === Use_::TYPE_FUNCTION || $use->type === Use_::TYPE_CONSTANT) {
            $this->removeNode($use);
        }
    }

    private function isAlreadyFullyQualified(ConstFetch|FuncCall $node): bool
    {
        $oldTokens = $this->file->getOldTokens();
        $startTokenPos = $node->getStartTokenPos();
        $name = $oldTokens[$startTokenPos][1] ?? null;

        if (! is_string($name)) {
            return false;
        }

        return str_starts_with($name, '\\');
    }

    /**
     * @param Use_[]|GroupUse[] $useNodes
     */
    private function getFullyQualifiedName(array $useNodes, ConstFetch|FuncCall $node): ?string
    {
        if (! $node->name instanceof Name) {
            return null;
        }

        $name = $node->name->toLowerString();
        $typeFilter = $node instanceof ConstFetch ? Use_::TYPE_CONSTANT : Use_::TYPE_FUNCTION;

        foreach ($useNodes as $useNode) {
            $prefix = $this->useImportsResolver->resolvePrefix($useNode);

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
}

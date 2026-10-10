<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Function_;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Function_\AddFunctionVoidReturnTypeWhereNoReturnRector\AddFunctionVoidReturnTypeWhereNoReturnRectorTest
 */
final class AddFunctionVoidReturnTypeWhereNoReturnRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private SilentVoidResolver $silentVoidResolver;
    public function __construct(SilentVoidResolver $silentVoidResolver)
    {
        $this->silentVoidResolver = $silentVoidResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add function return type void if there is no return', [new CodeSample(<<<'CODE_SAMPLE'
function restore() {
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function restore(): void {
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Function_::class];
    }
    /**
     * @param Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // already has return type → skip
        if ($node->returnType instanceof Node) {
            return null;
        }
        if (!$this->silentVoidResolver->hasExclusiveVoid($node)) {
            return null;
        }
        $node->returnType = new Identifier('void');
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::VOID_TYPE;
    }
}

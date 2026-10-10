<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector\AddClosureVoidReturnTypeWhereNoReturnRectorTest
 */
final class AddClosureVoidReturnTypeWhereNoReturnRector extends AbstractRector implements MinPhpVersionInterface
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
        return new RuleDefinition('Add closure return type void if there is no return', [new CodeSample(<<<'CODE_SAMPLE'
function () {
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function (): void {
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
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

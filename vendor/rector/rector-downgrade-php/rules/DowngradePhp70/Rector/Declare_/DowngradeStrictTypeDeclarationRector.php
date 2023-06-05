<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\Declare_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\NodeTraverser;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp70\Rector\Declare_\DowngradeStrictTypeDeclarationRector\DowngradeStrictTypeDeclarationRectorTest
 */
final class DowngradeStrictTypeDeclarationRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Declare_::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove the declare(strict_types=1)', [new CodeSample(<<<'CODE_SAMPLE'
declare(strict_types=1);
echo 'something';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo 'something';
CODE_SAMPLE
)]);
    }
    /**
     * @param Declare_ $node
     */
    public function refactor(Node $node) : ?int
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        return NodeTraverser::REMOVE_NODE;
    }
    private function shouldSkip(Declare_ $declare) : bool
    {
        foreach ($declare->declares as $singleDeclare) {
            if ($this->isName($singleDeclare->key, 'strict_types')) {
                return \false;
            }
        }
        return \true;
    }
}

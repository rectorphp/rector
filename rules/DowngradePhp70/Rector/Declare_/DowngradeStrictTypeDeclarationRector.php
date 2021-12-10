<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\Declare_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Declare_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp70\Rector\Declare_\DowngradeStrictTypeDeclarationRector\DowngradeStrictTypeDeclarationRectorTest
 */
final class DowngradeStrictTypeDeclarationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Declare_::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove the declare(strict_types=1)', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $this->removeNode($node);
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\Declare_ $declare) : bool
    {
        $declares = $declare->declares;
        foreach ($declares as $declare) {
            if ($this->isName($declare->key, 'strict_types')) {
                return \false;
            }
        }
        return \true;
    }
}

<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\GroupUse;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/group_use_declarations
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\GroupUse\SplitGroupedUseImportsRector\SplitGroupedUseImportsRectorTest
 */
final class SplitGroupedUseImportsRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor grouped use imports to standalone lines', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use SomeNamespace\{
    First,
    Second
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use SomeNamespace\First;
use SomeNamespace\Second;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\GroupUse::class];
    }
    /**
     * @param GroupUse $node
     * @return Use_[]
     */
    public function refactor(\PhpParser\Node $node) : array
    {
        $prefix = $this->getName($node->prefix);
        $uses = [];
        foreach ($node->uses as $useUse) {
            $useUse->name = new \PhpParser\Node\Name($prefix . '\\' . $this->getName($useUse->name));
            $uses[] = new \PhpParser\Node\Stmt\Use_([$useUse], $node->type);
        }
        return $uses;
    }
}

<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Include_;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symplify/CodingStandard#includerequire-should-be-followed-by-absolute-path
 *
 * @see \Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\AbsolutizeRequireAndIncludePathRectorTest
 */
final class AbsolutizeRequireAndIncludePathRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('include/require to absolute path. This Rector might introduce backwards incompatible code, when the include/require beeing changed depends on the current working directory.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        require 'autoload.php';

        require $variable;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        require __DIR__ . '/autoload.php';

        require $variable;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Include_::class];
    }
    /**
     * @param Include_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->expr instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        /** @var string $includeValue */
        $includeValue = $this->valueResolver->getValue($node->expr);
        // skip phar
        if (\strncmp($includeValue, 'phar://', \strlen('phar://')) === 0) {
            return null;
        }
        // skip absolute paths
        if (\strncmp($includeValue, '/', \strlen('/')) === 0) {
            return null;
        }
        // add preslash to string
        if (\strncmp($includeValue, './', \strlen('./')) === 0) {
            $node->expr->value = \RectorPrefix20211020\Nette\Utils\Strings::substring($includeValue, 1);
        } else {
            $node->expr->value = '/' . $includeValue;
        }
        $node->expr = new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Scalar\MagicConst\Dir(), $node->expr);
        return $node;
    }
}

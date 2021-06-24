<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/questions/55419673/php7-adding-a-slash-to-all-standard-php-functions-php-cs-fixer-rule
 *
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector\PreslashSimpleFunctionRectorTest
 */
final class PreslashSimpleFunctionRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add pre-slash to short named functions to improve performance', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function shorten($value)
    {
        return trim($value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function shorten($value)
    {
        return \trim($value);
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->name instanceof \PhpParser\Node\Name\FullyQualified) {
            return null;
        }
        $functionName = $this->getName($node);
        if ($functionName === null) {
            return null;
        }
        if (\strpos($functionName, '\\') !== \false) {
            return null;
        }
        $isDefinedPreviousCall = (bool) $this->betterNodeFinder->findFirstPreviousOfNode($node, function (\PhpParser\Node $node) use($functionName) : bool {
            if (!$node instanceof \PhpParser\Node\Stmt\Function_) {
                return \false;
            }
            return $this->isName($node->name, $functionName);
        });
        if ($isDefinedPreviousCall) {
            return null;
        }
        $node->name = new \PhpParser\Node\Name\FullyQualified($functionName);
        return $node;
    }
}

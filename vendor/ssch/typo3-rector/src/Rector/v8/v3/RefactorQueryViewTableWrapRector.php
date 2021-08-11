<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v3;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.3/Deprecation-77557-MethodQueryView-tableWrap.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v3\RefactorQueryViewTableWrapRector\RefactorQueryViewTableWrapRectorTest
 */
final class RefactorQueryViewTableWrapRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Database\\QueryView'))) {
            return null;
        }
        if (!$this->isName($node->name, 'tableWrap')) {
            return null;
        }
        /** @var Arg[] $args */
        $args = $node->args;
        $firstArgument = \array_shift($args);
        if (!$firstArgument instanceof \PhpParser\Node\Arg) {
            return null;
        }
        return new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Scalar\String_('<pre>'), $firstArgument->value), new \PhpParser\Node\Scalar\String_('</pre>'));
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate the method QueryView->tableWrap() to use pre-Tag', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$queryView = GeneralUtility::makeInstance(QueryView::class);
$output = $queryView->tableWrap('value');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$queryView = GeneralUtility::makeInstance(QueryView::class);
$output = '<pre>' . 'value' . '</pre>';
CODE_SAMPLE
)]);
    }
}

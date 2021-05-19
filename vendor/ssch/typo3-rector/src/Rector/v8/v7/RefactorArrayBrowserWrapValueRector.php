<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v7;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.7/Deprecation-80440-EXTlowlevelArrayBrowser-wrapValue.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v7\RefactorArrayBrowserWrapValueRector\RefactorArrayBrowserWrapValueRectorTest
 */
final class RefactorArrayBrowserWrapValueRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Lowlevel\\Utility\\ArrayBrowser'))) {
            return null;
        }
        if (!$this->isName($node->name, 'wrapValue')) {
            return null;
        }
        /** @var Arg[] $args */
        $args = $node->args;
        $firstArgument = \array_shift($args);
        return $this->nodeFactory->createFuncCall('htmlspecialchars', [$firstArgument]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate the method ArrayBrowser->wrapValue() to use htmlspecialchars()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$arrayBrowser = GeneralUtility::makeInstance(ArrayBrowser::class);
$arrayBrowser->wrapValue('value');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$arrayBrowser = GeneralUtility::makeInstance(ArrayBrowser::class);
htmlspecialchars('value');
CODE_SAMPLE
)]);
    }
}

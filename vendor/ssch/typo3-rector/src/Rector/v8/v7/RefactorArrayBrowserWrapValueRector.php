<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.7/Deprecation-80440-EXTlowlevelArrayBrowser-wrapValue.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v7\RefactorArrayBrowserWrapValueRector\RefactorArrayBrowserWrapValueRectorTest
 */
final class RefactorArrayBrowserWrapValueRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Lowlevel\\Utility\\ArrayBrowser'))) {
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate the method ArrayBrowser->wrapValue() to use htmlspecialchars()', [new CodeSample(<<<'CODE_SAMPLE'
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

<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220531\TYPO3\CMS\Core\Imaging\GraphicalFunctions;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Breaking-72342-RemovedDeprecatedCodeFromGeneralUtility.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\RefactorRemovedMethodsFromGeneralUtilityRector\RefactorRemovedMethodsFromGeneralUtilityRectorTest
 */
final class RefactorRemovedMethodsFromGeneralUtilityRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * List of nodes this class checks, classes that implements \PhpParser\Node See beautiful map of all nodes
     * https://github.com/rectorphp/rector/blob/master/docs/NodesOverview.md.
     *
     * @return string[]
     */
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node->class, 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility')) {
            return null;
        }
        $methodName = $this->getName($node->name);
        if (null === $methodName) {
            return null;
        }
        if ('gif_compress' === $methodName) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions', 'gifCompress', $node->args);
        }
        if ('png_to_gif_by_imagemagick' === $methodName) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions', 'pngToGifByImagemagick', $node->args);
        }
        if ('read_png_gif' === $methodName) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions', 'readPngGif', $node->args);
        }
        if ('array_merge' === $methodName) {
            [$arg1, $arg2] = $node->args;
            return new \PhpParser\Node\Expr\BinaryOp\Plus($arg1->value, $arg2->value);
        }
        if ('cleanOutputBuffers' === $methodName) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'flushOutputBuffers');
        }
        if (\in_array($methodName, ['inArray', 'removeArrayEntryByValue', 'keepItemsInArray', 'remapArrayKeys', 'arrayDiffAssocRecursive', 'naturalKeySortRecursive'], \true)) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\ArrayUtility', $methodName, $node->args);
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor removed methods from GeneralUtility.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('GeneralUtility::gif_compress();', \RectorPrefix20220531\TYPO3\CMS\Core\Imaging\GraphicalFunctions::class . '::gifCompress();')]);
    }
}

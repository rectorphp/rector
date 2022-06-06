<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Plus;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220606\TYPO3\CMS\Core\Imaging\GraphicalFunctions;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Breaking-72342-RemovedDeprecatedCodeFromGeneralUtility.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\RefactorRemovedMethodsFromGeneralUtilityRector\RefactorRemovedMethodsFromGeneralUtilityRectorTest
 */
final class RefactorRemovedMethodsFromGeneralUtilityRector extends AbstractRector
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
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
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
            return new Plus($arg1->value, $arg2->value);
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor removed methods from GeneralUtility.', [new CodeSample('GeneralUtility::gif_compress();', GraphicalFunctions::class . '::gifCompress();')]);
    }
}

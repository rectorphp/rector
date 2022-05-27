<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220527\TYPO3\CMS\Core\Imaging\GraphicalFunctions;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Deprecation-74022-GraphicalFunctions-prependAbsolutePath.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\PrependAbsolutePathToGetFileAbsFileNameRector\PrependAbsolutePathToGetFileAbsFileNameRectorTest
 */
final class PrependAbsolutePathToGetFileAbsFileNameRector extends AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions'))) {
            return null;
        }
        if (!$this->isName($node->name, 'prependAbsolutePath')) {
            return null;
        }
        return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'getFileAbsFileName', $node->args);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use GeneralUtility::getFileAbsFileName() instead of GraphicalFunctions->prependAbsolutePath()', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;

class SomeFooBar
{
    private $graphicalFunctions;

    public function __construct(GraphicalFunctions $graphicalFunctions)
    {
        $this->graphicalFunctions = $graphicalFunctions;
        $this->graphicalFunctions->prependAbsolutePath('some.font');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;

class SomeFooBar
{
    private $graphicalFunctions;

    public function __construct(GraphicalFunctions $graphicalFunctions)
    {
        $this->graphicalFunctions = $graphicalFunctions;
        GeneralUtility::getFileAbsFileName('some.font');
    }
}
CODE_SAMPLE
)]);
    }
}

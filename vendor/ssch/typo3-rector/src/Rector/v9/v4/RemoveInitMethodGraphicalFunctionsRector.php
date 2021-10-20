<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\TYPO3\CMS\Core\Imaging\GraphicalFunctions;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85978-GraphicalFunctions-init.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\RemoveInitMethodGraphicalFunctionsRector\RemoveInitMethodGraphicalFunctionsRectorTest
 */
final class RemoveInitMethodGraphicalFunctionsRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions'))) {
            return null;
        }
        if (!$this->isName($node->name, 'init')) {
            return null;
        }
        $this->removeNode($node);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove method call init of class GraphicalFunctions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$graphicalFunctions = GeneralUtility::makeInstance(GraphicalFunctions::class);
$graphicalFunctions->init();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$graphicalFunctions = GeneralUtility::makeInstance(GraphicalFunctions::class);
CODE_SAMPLE
)]);
    }
}

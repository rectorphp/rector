<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v3;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.3/Deprecation-90260-ResourceFactorygetInstancePseudo-factory.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v3\SubstituteResourceFactoryRector\SubstituteResourceFactoryRectorTest
 */
final class SubstituteResourceFactoryRector extends \Rector\Core\Rector\AbstractRector
{
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Resource\\ResourceFactory'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getInstance')) {
            return null;
        }
        return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Resource\\ResourceFactory')]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Substitue ResourceFactory::getInstance() through GeneralUtility::makeInstance(ResourceFactory::class)', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$resourceFactory = ResourceFactory::getInstance();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
CODE_SAMPLE
)]);
    }
}

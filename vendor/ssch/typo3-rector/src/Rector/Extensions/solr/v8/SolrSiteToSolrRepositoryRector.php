<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\Extensions\solr\v8;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/TYPO3-Solr/ext-solr/issues/1138
 * @see \Ssch\TYPO3Rector\Tests\Rector\Extensions\solr\v8\SolrSiteToSolrRepositoryRector\SolrSiteToSolrRepositoryRectorTest
 */
final class SolrSiteToSolrRepositoryRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use SiteRepository instead of instantiating class Site directly with page id', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$site1 = GeneralUtility::makeInstance(Site::class, 1);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$site1 = GeneralUtility::makeInstance(SiteRepository::class)->getSiteByPageId(1);
CODE_SAMPLE
)]);
    }
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'makeInstance')) {
            return null;
        }
        if (\count($node->args) <= 1) {
            return null;
        }
        $firstArgument = $node->args[0];
        $secondArgument = $node->args[1];
        $firstArgumentValue = $this->valueResolver->getValue($firstArgument->value);
        if (!\is_string($firstArgumentValue)) {
            return null;
        }
        $firstArgumentObjectType = new \PHPStan\Type\ObjectType($firstArgumentValue);
        if ($firstArgumentObjectType->isInstanceOf('ApacheSolrForTypo3\\Solr\\Site')->no()) {
            return null;
        }
        $secondArgumentValue = $this->valueResolver->getValue($secondArgument->value);
        if (!\is_numeric($secondArgumentValue)) {
            return null;
        }
        $node->args[0]->value = $this->nodeFactory->createClassConstReference('ApacheSolrForTypo3\\Solr\\Domain\\Site\\SiteRepository');
        unset($node->args[1]);
        return $this->nodeFactory->createMethodCall($node, 'getSiteByPageId', [$secondArgument]);
    }
}

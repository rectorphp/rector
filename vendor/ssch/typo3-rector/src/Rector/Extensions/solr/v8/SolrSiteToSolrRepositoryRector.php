<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\Extensions\solr\v8;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/TYPO3-Solr/ext-solr/issues/1138
 * @see \Ssch\TYPO3Rector\Tests\Rector\Extensions\solr\v8\SolrSiteToSolrRepositoryRector\SolrSiteToSolrRepositoryRectorTest
 */
final class SolrSiteToSolrRepositoryRector extends AbstractRector
{
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use SiteRepository instead of instantiating class Site directly with page id', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
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
        $firstArgumentObjectType = new ObjectType($firstArgumentValue);
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

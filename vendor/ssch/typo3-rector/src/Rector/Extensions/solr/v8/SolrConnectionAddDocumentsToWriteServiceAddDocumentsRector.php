<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\Extensions\solr\v8;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/TYPO3-Solr/ext-solr/commit/523aff17187ecc25e4cad83e7d20af31329ecd6c#diff-563034ee193649001177d14f15ce4dcb24cbe39ecd0de99588fe2ec841b283fe
 * @see \Ssch\TYPO3Rector\Tests\Rector\Extensions\solr\v8\SolrConnectionAddDocumentsToWriteServiceAddDocumentsRector\SolrConnectionAddDocumentsToWriteServiceAddDocumentsTest
 */
final class SolrConnectionAddDocumentsToWriteServiceAddDocumentsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use method addDocuments from WriteService of SolrConnection class', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$this->solrConnection->addDocuments([]);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$this->solrConnection->getWriteService()->addDocuments([]);
CODE_SAMPLE
)]);
    }
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('ApacheSolrForTypo3\\Solr\\System\\Solr\\SolrConnection'))) {
            return null;
        }
        if (!$this->isName($node->name, 'addDocuments')) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($node->var, 'getWriteService'), 'addDocuments', $node->args);
    }
}

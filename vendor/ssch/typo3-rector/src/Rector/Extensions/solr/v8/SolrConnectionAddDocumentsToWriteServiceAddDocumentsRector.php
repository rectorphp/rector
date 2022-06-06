<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\Extensions\solr\v8;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/TYPO3-Solr/ext-solr/commit/523aff17187ecc25e4cad83e7d20af31329ecd6c#diff-563034ee193649001177d14f15ce4dcb24cbe39ecd0de99588fe2ec841b283fe
 * @see \Ssch\TYPO3Rector\Tests\Rector\Extensions\solr\v8\SolrConnectionAddDocumentsToWriteServiceAddDocumentsRector\SolrConnectionAddDocumentsToWriteServiceAddDocumentsTest
 */
final class SolrConnectionAddDocumentsToWriteServiceAddDocumentsRector extends AbstractRector
{
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use method addDocuments from WriteService of SolrConnection class', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('ApacheSolrForTypo3\\Solr\\System\\Solr\\SolrConnection'))) {
            return null;
        }
        if (!$this->isName($node->name, 'addDocuments')) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($node->var, 'getWriteService'), 'addDocuments', $node->args);
    }
}

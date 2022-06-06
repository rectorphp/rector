<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\Extensions\solr\v9;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/p/apache-solr-for-typo3/solr/10.0/en-us/Releases/solr-release-9-0.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\Extensions\solr\v9\ApacheSolrDocumentToSolariumDocumentRector\ApacheSolrDocumentToSolariumDocumentRectorTest
 */
final class ApacheSolrDocumentToSolariumDocumentRector extends AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('Apache_Solr_Document'))) {
            return null;
        }
        if (!$this->isName($node->name, 'setMultiValue')) {
            return null;
        }
        $node->name = new Identifier('addField');
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Apache_Solr_Document to solarium based document', [new CodeSample(<<<'CODE_SAMPLE'
$document = new Apache_Solr_Document();
$document->setMultiValue('foo', 'bar', true);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$document = new Apache_Solr_Document();
$document->addField('foo', 'bar', true);
CODE_SAMPLE
)]);
    }
}

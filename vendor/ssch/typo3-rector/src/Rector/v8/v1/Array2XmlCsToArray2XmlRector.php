<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v1;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Concat;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.1/Deprecation-75371-Array2xml_cs.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v1\Array2XmlCsToArray2XmlRector\Array2XmlCsToArray2XmlRectorTest
 */
final class Array2XmlCsToArray2XmlRector extends AbstractRector
{
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
        if (!$this->isName($node->name, 'array2xml_cs')) {
            return null;
        }
        $node->name = new Identifier('array2xml');
        $args = $node->args;
        $array = isset($args[0]) ? $this->valueResolver->getValue($args[0]->value) : [];
        $doctag = isset($args[1]) ? $this->valueResolver->getValue($args[1]->value) : 'phparray';
        $options = isset($args[2]) ? $this->valueResolver->getValue($args[2]->value) : [];
        $charset = isset($args[3]) ? $this->valueResolver->getValue($args[3]->value) : 'utf-8';
        $node->args = $this->nodeFactory->createArgs([$array, '', 0, $doctag, 0, $options]);
        return new Concat(new Concat(new Concat(new Concat(new String_('<?xml version="1.0" encoding="'), $this->nodeFactory->createFuncCall('htmlspecialchars', $this->nodeFactory->createArgs([$charset]))), new String_('" standalone="yes" ?>')), new ConstFetch(new Name('LF'))), $node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('array2xml_cs to array2xml', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;

GeneralUtility::array2xml_cs();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;

GeneralUtility::array2xml();
CODE_SAMPLE
)]);
    }
}

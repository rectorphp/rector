<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v1;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.1/Deprecation-75371-Array2xml_cs.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v1\Array2XmlCsToArray2XmlRector\Array2XmlCsToArray2XmlRectorTest
 */
final class Array2XmlCsToArray2XmlRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'array2xml_cs')) {
            return null;
        }
        $node->name = new \PhpParser\Node\Identifier('array2xml');
        $args = $node->args;
        $array = isset($args[0]) ? $this->valueResolver->getValue($args[0]->value) : [];
        $doctag = isset($args[1]) ? $this->valueResolver->getValue($args[1]->value) : 'phparray';
        $options = isset($args[2]) ? $this->valueResolver->getValue($args[2]->value) : [];
        $charset = isset($args[3]) ? $this->valueResolver->getValue($args[3]->value) : 'utf-8';
        $node->args = $this->nodeFactory->createArgs([$array, '', 0, $doctag, 0, $options]);
        return new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Scalar\String_('<?xml version="1.0" encoding="'), $this->nodeFactory->createFuncCall('htmlspecialchars', $this->nodeFactory->createArgs([$charset]))), new \PhpParser\Node\Scalar\String_('" standalone="yes" ?>')), new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('LF'))), $node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('array2xml_cs to array2xml', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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

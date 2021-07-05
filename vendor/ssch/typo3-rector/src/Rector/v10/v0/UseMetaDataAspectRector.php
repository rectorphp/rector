<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Deprecation-85895-DeprecateFile_getMetaData.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v0\UseMetaDataAspectRector\UseMetaDataAspectRectorTest
 */
final class UseMetaDataAspectRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Resource\\File'))) {
            return null;
        }
        if (!$this->isName($node->name, '_getMetaData')) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($node->var, 'getMetaData'), 'get');
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use $fileObject->getMetaData()->get() instead of $fileObject->_getMetaData()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$fileObject = new File();
$fileObject->_getMetaData();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$fileObject = new File();
$fileObject->getMetaData()->get();
CODE_SAMPLE
)]);
    }
}

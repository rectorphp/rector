<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Deprecation-85895-DeprecateFile_getMetaData.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v0\UseMetaDataAspectRector\UseMetaDataAspectRectorTest
 */
final class UseMetaDataAspectRector extends AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Resource\\File'))) {
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use $fileObject->getMetaData()->get() instead of $fileObject->_getMetaData()', [new CodeSample(<<<'CODE_SAMPLE'
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

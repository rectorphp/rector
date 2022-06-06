<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220606\TYPO3\CMS\Extbase\Utility\TypeHandlingUtility;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Deprecation-87613-DeprecateTYPO3CMSExtbaseUtilityTypeHandlingUtilityhex2bin.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v0\UseNativePhpHex2binMethodRector\UseNativePhpHex2binMethodRectorTest
 */
final class UseNativePhpHex2binMethodRector extends AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Extbase\\Utility\\TypeHandlingUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'hex2bin')) {
            return null;
        }
        return $this->nodeFactory->createFuncCall('hex2bin', $node->args);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns \\TYPO3\\CMS\\Extbase\\Utility\\TypeHandlingUtility::hex2bin calls to native php hex2bin', [new CodeSample(TypeHandlingUtility::class . '::hex2bin("6578616d706c65206865782064617461");', 'hex2bin("6578616d706c65206865782064617461");')]);
    }
}

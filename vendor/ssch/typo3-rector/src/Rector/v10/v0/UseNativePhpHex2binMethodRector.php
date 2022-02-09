<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220209\TYPO3\CMS\Extbase\Utility\TypeHandlingUtility;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Deprecation-87613-DeprecateTYPO3CMSExtbaseUtilityTypeHandlingUtilityhex2bin.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v0\UseNativePhpHex2binMethodRector\UseNativePhpHex2binMethodRectorTest
 */
final class UseNativePhpHex2binMethodRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Utility\\TypeHandlingUtility'))) {
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
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns \\TYPO3\\CMS\\Extbase\\Utility\\TypeHandlingUtility::hex2bin calls to native php hex2bin', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(\RectorPrefix20220209\TYPO3\CMS\Extbase\Utility\TypeHandlingUtility::class . '::hex2bin("6578616d706c65206865782064617461");', 'hex2bin("6578616d706c65206865782064617461");')]);
    }
}

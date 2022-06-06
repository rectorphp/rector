<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v1;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\StringUtility;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.1/Deprecation-88995-CallingRegisterPluginWithVendorName.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v1\RegisterPluginWithVendorNameRector\RegisterPluginWithVendorNameRectorTest
 */
final class RegisterPluginWithVendorNameRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Utility\\ExtensionUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'registerPlugin')) {
            return null;
        }
        return $this->removeVendorNameIfNeeded($node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove vendor name from registerPlugin call', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('TYPO3\\CMS\\Extbase\\Utility\\ExtensionUtility' . '::registerPlugin(
   \'TYPO3.CMS.Form\',
   \'Formframework\',
   \'Form\',
   \'content-form\',
);', 'TYPO3\\CMS\\Extbase\\Utility\\ExtensionUtility' . '::registerPlugin(
   \'Form\',
   \'Formframework\',
   \'Form\',
   \'content-form\',
);')]);
    }
    private function removeVendorNameIfNeeded(\PhpParser\Node\Expr\StaticCall $staticCall) : ?\PhpParser\Node
    {
        $extensionNameArgumentValue = $staticCall->getArgs()[0]->value;
        $extensionName = $this->valueResolver->getValue($extensionNameArgumentValue);
        $fileInfo = $this->file->getSmartFileInfo();
        if ($extensionNameArgumentValue instanceof \PhpParser\Node\Expr\BinaryOp\Concat && $this->isPotentiallyUndefinedExtensionKeyVariable($extensionNameArgumentValue)) {
            $extensionName = $this->valueResolver->getValue($extensionNameArgumentValue->left) . \basename($fileInfo->getRelativeDirectoryPath());
        }
        if (!\is_string($extensionName)) {
            return null;
        }
        $delimiterPosition = \strrpos($extensionName, '.');
        if (\false === $delimiterPosition) {
            return null;
        }
        $extensionName = \Ssch\TYPO3Rector\Helper\StringUtility::prepareExtensionName($extensionName, $delimiterPosition);
        $staticCall->args[0] = $this->nodeFactory->createArg($extensionName);
        return $staticCall;
    }
    private function isPotentiallyUndefinedExtensionKeyVariable(\PhpParser\Node\Expr\BinaryOp\Concat $extensionNameArgumentValue) : bool
    {
        if (!$extensionNameArgumentValue->right instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        if (null !== $this->valueResolver->getValue($extensionNameArgumentValue->right)) {
            return \false;
        }
        return $this->isNames($extensionNameArgumentValue->right, ['_EXTKEY', 'extensionKey']);
    }
}

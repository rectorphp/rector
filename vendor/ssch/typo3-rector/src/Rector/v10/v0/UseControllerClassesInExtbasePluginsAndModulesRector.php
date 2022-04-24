<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\StringUtility;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Deprecation-87550-UseControllerClassesWhenRegisteringPluginsmodules.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v0\UseControllerClassesInExtbasePluginsAndModulesRector\UseControllerClassesInExtbasePluginsAndModulesRectorTest
 */
final class UseControllerClassesInExtbasePluginsAndModulesRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->isNames($node->name, ['configurePlugin', 'registerModule'])) {
            return null;
        }
        $extensionNameArgumentValue = $node->args[0]->value;
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
        $vendorName = $this->prepareVendorName($extensionName, $delimiterPosition);
        $extensionName = \Ssch\TYPO3Rector\Helper\StringUtility::prepareExtensionName($extensionName, $delimiterPosition);
        if ('' === $extensionName) {
            return null;
        }
        $node->args[0] = $this->nodeFactory->createArg($extensionName);
        if ($this->isName($node->name, 'configurePlugin')) {
            $this->refactorConfigurePluginMethod($node, $vendorName, $extensionName);
            return null;
        }
        $this->refactorRegisterPluginMethod($node, $vendorName, $extensionName);
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use controller classes when registering extbase plugins/modules', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
ExtensionUtility::configurePlugin(
    'TYPO3.CMS.Form',
    'Formframework',
    ['FormFrontend' => 'render, perform'],
    ['FormFrontend' => 'perform'],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;ExtensionUtility::configurePlugin(
    'Form',
    'Formframework',
    [\TYPO3\CMS\Form\Controller\FormFrontendController::class => 'render, perform'],
    [\TYPO3\CMS\Form\Controller\FormFrontendController::class => 'perform'],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
CODE_SAMPLE
)]);
    }
    private function getControllerClassName(string $vendor, string $extensionKey, string $subPackageKey, string $controllerAlias) : string
    {
        $objectName = \str_replace(['@extension', '@subpackage', '@controller', '@vendor', '\\\\'], [$extensionKey, $subPackageKey, $controllerAlias, $vendor, '\\'], '@vendor\\@extension\\@subpackage\\Controller\\@controllerController');
        return \trim($objectName, '\\');
    }
    private function createNewControllerActionsArray(\PhpParser\Node\Expr\Array_ $controllerActions, string $vendorName, string $extensionName) : void
    {
        foreach ($controllerActions->items as $controllerActions) {
            if (!$controllerActions instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $controllerActions->key) {
                continue;
            }
            $controllerClassName = $this->valueResolver->getValue($controllerActions->key);
            if (null === $controllerClassName) {
                continue;
            }
            $controllerClassNameType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($controllerActions->key);
            if ($controllerClassNameType instanceof \PHPStan\Type\Constant\ConstantStringType) {
                // Already transformed
                continue;
            }
            $controllerActions->key = $this->nodeFactory->createClassConstReference($this->getControllerClassName($vendorName, $extensionName, '', $controllerClassName));
        }
    }
    private function refactorConfigurePluginMethod(\PhpParser\Node\Expr\StaticCall $staticCall, string $vendorName, string $extensionName) : void
    {
        if (isset($staticCall->args[2]) && $staticCall->args[2]->value instanceof \PhpParser\Node\Expr\Array_) {
            $this->createNewControllerActionsArray($staticCall->args[2]->value, $vendorName, $extensionName);
        }
        if (isset($staticCall->args[3]) && $staticCall->args[3]->value instanceof \PhpParser\Node\Expr\Array_) {
            $this->createNewControllerActionsArray($staticCall->args[3]->value, $vendorName, $extensionName);
        }
    }
    private function refactorRegisterPluginMethod(\PhpParser\Node\Expr\StaticCall $staticCall, string $vendorName, string $extensionName) : void
    {
        if (isset($staticCall->args[4]) && $staticCall->args[4]->value instanceof \PhpParser\Node\Expr\Array_) {
            $this->createNewControllerActionsArray($staticCall->args[4]->value, $vendorName, $extensionName);
        }
    }
    private function isPotentiallyUndefinedExtensionKeyVariable(\PhpParser\Node\Expr\BinaryOp\Concat $extensionNameArgumentValue) : bool
    {
        if (!$extensionNameArgumentValue->right instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        return null === $this->valueResolver->getValue($extensionNameArgumentValue->right);
    }
    private function prepareVendorName(string $extensionName, int $delimiterPosition) : string
    {
        return \str_replace('.', '\\', \substr($extensionName, 0, $delimiterPosition));
    }
}

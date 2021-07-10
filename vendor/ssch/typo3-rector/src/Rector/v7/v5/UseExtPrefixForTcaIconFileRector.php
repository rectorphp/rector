<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v7\v5;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.5/Deprecation-69754-TcaCtrlIconfileUsingRelativePathToExtAndFilenameOnly.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v7\v5\UseExtPrefixForTcaIconFileRector\UseExtPrefixForTcaIconFileRectorTest
 */
final class UseExtPrefixForTcaIconFileRector extends \Rector\Core\Rector\AbstractRector
{
    use TcaHelperTrait;
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Deprecate relative path to extension directory and using filename only in TCA ctrl iconfile', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
[
    'ctrl' => [
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('my_extension') . 'Resources/Public/Icons/image.png'
    ]
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
[
    'ctrl' => [
        'iconfile' => 'EXT:my_extension/Resources/Public/Icons/image.png'
    ]
];
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isFullTca($node)) {
            return null;
        }
        $ctrl = $this->extractCtrl($node);
        if (!$ctrl instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        $ctrlItems = $ctrl->value;
        if (!$ctrlItems instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        foreach ($ctrlItems->items as $fieldValue) {
            if (!$fieldValue instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $fieldValue->key) {
                continue;
            }
            if ($this->valueResolver->isValue($fieldValue->key, 'iconfile')) {
                $this->refactorIconFile($fieldValue);
                return $node;
            }
        }
        return null;
    }
    private function refactorIconFile(\PhpParser\Node\Expr\ArrayItem $fieldValue) : void
    {
        if (null === $fieldValue->value) {
            return;
        }
        if ($fieldValue->value instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            $staticCall = $fieldValue->value->left;
            if (!$staticCall instanceof \PhpParser\Node\Expr\StaticCall) {
                return;
            }
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($staticCall, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility'))) {
                return;
            }
            if (!$this->isName($staticCall->name, 'extRelPath')) {
                return;
            }
            if (!isset($staticCall->args[0])) {
                return;
            }
            $extensionKey = $this->valueResolver->getValue($staticCall->args[0]->value);
            if (null === $extensionKey) {
                return;
            }
            $pathToFileNode = $fieldValue->value->right;
            if (!$pathToFileNode instanceof \PhpParser\Node\Scalar\String_) {
                return;
            }
            $pathToFile = $this->valueResolver->getValue($pathToFileNode);
            if (null === $pathToFile) {
                return;
            }
            $fieldValue->value = new \PhpParser\Node\Scalar\String_(\sprintf('EXT:%s/%s', $extensionKey, \ltrim($pathToFile, '/')));
        }
        if (!$fieldValue->value instanceof \PhpParser\Node\Scalar\String_) {
            return;
        }
        $pathToFile = $this->valueResolver->getValue($fieldValue->value);
        if (null === $pathToFile) {
            return;
        }
        if (\strpos($pathToFile, '/') !== \false) {
            return;
        }
        $fieldValue->value = new \PhpParser\Node\Scalar\String_(\sprintf('EXT:t3skin/icons/gfx/i/%s', $pathToFile));
    }
}

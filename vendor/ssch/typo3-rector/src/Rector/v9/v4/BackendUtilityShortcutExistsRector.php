<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220418\TYPO3\CMS\Backend\Utility\BackendUtility;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-84414-BackendUtilityshortcutExists.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\BackendUtilityShortcutExistsRector\BackendUtilityShortcutExistsRectorTest
 */
final class BackendUtilityShortcutExistsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('shortcutExists Static call replaced by method call of ShortcutRepository', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(\RectorPrefix20220418\TYPO3\CMS\Backend\Utility\BackendUtility::class . '::shortcutExists($url);', <<<'CODE_SAMPLE'
GeneralUtility::makeInstance(ShortcutRepository::class)->shortcutExists($url);
CODE_SAMPLE
)]);
    }
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Utility\\BackendUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'shortcutExists')) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Backend\\Backend\\Shortcut\\ShortcutRepository')]), 'shortcutExists', $node->args);
    }
}

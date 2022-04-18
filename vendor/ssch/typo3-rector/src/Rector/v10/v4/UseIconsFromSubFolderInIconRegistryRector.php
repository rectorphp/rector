<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v4;

use RectorPrefix20220418\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/TYPO3/typo3/tree/v10.4.10/typo3/sysext/core/Resources/Public/Icons/T3Icons/svgs
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v4\UseIconsFromSubFolderInIconRegistryRector\UseIconsFromSubFolderInIconRegistryRectorTest
 */
final class UseIconsFromSubFolderInIconRegistryRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const SOURCE = 'source';
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Imaging\\IconRegistry'))) {
            return null;
        }
        if (!$this->isName($node->name, 'registerIcon')) {
            return null;
        }
        if (!$this->isSvgIconProvider($node)) {
            return null;
        }
        $options = $this->valueResolver->getValue($node->args[2]->value);
        if (!\is_array($options)) {
            return null;
        }
        if (!\array_key_exists(self::SOURCE, $options)) {
            return null;
        }
        if (\strncmp($options[self::SOURCE], 'typo3/sysext/core/Resources/Public/Icons/T3Icons/content/', \strlen('typo3/sysext/core/Resources/Public/Icons/T3Icons/content/')) !== 0) {
            return null;
        }
        $options[self::SOURCE] = \RectorPrefix20220418\Nette\Utils\Strings::replace($options[self::SOURCE], '#typo3/sysext/core/Resources/Public/Icons/T3Icons/content/#i', 'typo3/sysext/core/Resources/Public/Icons/T3Icons/svgs/content/');
        $node->args[2]->value = $this->nodeFactory->createArray($options);
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use icons from subfolder in IconRegistry', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)
        ->registerIcon(
            'apps-pagetree-reference',
            TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            [
                'source' => 'typo3/sysext/core/Resources/Public/Icons/T3Icons/content/content-text.svg',
            ]
        );
CODE_SAMPLE
, <<<'CODE_SAMPLE'
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)
        ->registerIcon(
            'apps-pagetree-reference',
            TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            [
                'source' => 'typo3/sysext/core/Resources/Public/Icons/T3Icons/svgs/content/content-text.svg',
            ]
        );
CODE_SAMPLE
)]);
    }
    private function isSvgIconProvider(\PhpParser\Node\Expr\MethodCall $node) : bool
    {
        $iconProviderClassName = $this->valueResolver->getValue($node->args[1]->value);
        if (null === $iconProviderClassName) {
            return \false;
        }
        $iconProviderClassNameObjectType = new \PHPStan\Type\ObjectType($iconProviderClassName);
        return $iconProviderClassNameObjectType->equals(new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Imaging\\IconProvider\\SvgIconProvider'));
    }
}

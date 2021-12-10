<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v7\v4;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.4/Deprecation-67991-RemovedExtCms.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v7\v4\MoveLanguageFilesFromRemovedCmsExtensionRector\MoveLanguageFilesFromRemovedCmsExtensionRectorTest
 */
final class MoveLanguageFilesFromRemovedCmsExtensionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const MAPPING_OLD_TO_NEW_PATHS = ['cms/web_info/locallang.xlf' => 'frontend/Resources/Private/Language/locallang_webinfo.xlf', 'cms/locallang_ttc.xlf' => 'frontend/Resources/Private/Language/locallang_ttc.xlf', 'cms/locallang_tca.xlf' => 'frontend/Resources/Private/Language/locallang_tca.xlf', 'cms/layout/locallang_db_new_content_el.xlf' => 'backend/Resources/Private/Language/locallang_db_new_content_el.xlf', 'cms/layout/locallang.xlf' => 'backend/Resources/Private/Language/locallang_layout.xlf', 'cms/layout/locallang_mod.xlf' => 'backend/Resources/Private/Language/locallang_mod.xlf', 'cms/locallang_csh_webinfo.xlf' => 'frontend/Resources/Private/Language/locallang_csh_webinfo.xlf', 'cms/locallang_csh_weblayout.xlf' => 'frontend/Resources/Private/Language/locallang_csh_weblayout.xlf'];
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move language files of removed cms to new location', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Localization\LanguageService;
$languageService = new LanguageService();
$languageService->sL('LLL:EXT:cms/web_info/locallang.xlf:pages_1');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Localization\LanguageService;
$languageService = new LanguageService();
$languageService->sL('LLL:EXT:frontend/Resources/Private/Language/locallang_webinfo.xlf:pages_1');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Scalar\String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $value = $this->valueResolver->getValue($node);
        if (null === $value || !\is_string($value)) {
            return null;
        }
        foreach (self::MAPPING_OLD_TO_NEW_PATHS as $oldPath => $newPath) {
            $oldPathPrefixed = \sprintf('LLL:EXT:%s', $oldPath);
            if (\strpos($value, $oldPathPrefixed) !== \false) {
                $newPathPrefixed = \sprintf('LLL:EXT:%s', $newPath);
                return new \PhpParser\Node\Scalar\String_(\str_replace($oldPathPrefixed, $newPathPrefixed, $value));
            }
        }
        return null;
    }
}

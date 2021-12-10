<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v3;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.3/Deprecation-84680-MoveLastLanguageFilesAwayFromExtlangAndRemoveExtlangCompletely.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v3\MoveLanguageFilesFromExtensionLangRector\MoveLanguageFilesFromExtensionLangRectorTest
 */
final class MoveLanguageFilesFromExtensionLangRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const MAPPING_OLD_TO_NEW_PATHS = ['lang/Resources/Private/Language/locallang_alt_intro.xlf' => 'about/Resources/Private/Language/Modules/locallang_alt_intro.xlf', 'lang/Resources/Private/Language/locallang_alt_doc.xlf' => 'backend/Resources/Private/Language/locallang_alt_doc.xlf', 'lang/Resources/Private/Language/locallang_login.xlf' => 'backend/Resources/Private/Language/locallang_login.xlf', 'lang/Resources/Private/Language/locallang_common.xlf' => 'core/Resources/Private/Language/locallang_common.xlf', 'lang/Resources/Private/Language/locallang_core.xlf' => 'core/Resources/Private/Language/locallang_core.xlf', 'lang/Resources/Private/Language/locallang_general.xlf' => 'core/Resources/Private/Language/locallang_general.xlf', 'lang/Resources/Private/Language/locallang_misc.xlf' => 'core/Resources/Private/Language/locallang_misc.xlf', 'lang/Resources/Private/Language/locallang_mod_web_list.xlf' => 'core/Resources/Private/Language/locallang_mod_web_list.xlf', 'lang/Resources/Private/Language/locallang_tca.xlf' => 'core/Resources/Private/Language/locallang_tca.xlf', 'lang/Resources/Private/Language/locallang_tsfe.xlf' => 'core/Resources/Private/Language/locallang_tsfe.xlf', 'lang/Resources/Private/Language/locallang_wizards.xlf' => 'core/Resources/Private/Language/locallang_wizards.xlf', 'lang/Resources/Private/Language/locallang_browse_links.xlf' => 'recordlist/Resources/Private/Language/locallang_browse_links.xlf', 'lang/Resources/Private/Language/locallang_tcemain.xlf' => 'workspaces/Resources/Private/Language/locallang_tcemain.xlf'];
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
            if (\strpos($value, $oldPath) !== \false) {
                return new \PhpParser\Node\Scalar\String_(\str_replace($oldPath, $newPath, $value));
            }
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move language resources from ext:lang to their new locations', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Localization\LanguageService;
$languageService = new LanguageService();
$languageService->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:labels.no_title');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Localization\LanguageService;
$languageService = new LanguageService();
$languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.no_title');
CODE_SAMPLE
)]);
    }
}

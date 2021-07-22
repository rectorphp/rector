<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Yaml\Form\Rector;

use Ssch\TYPO3Rector\Contract\FileProcessor\Yaml\Form\FormYamlRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-87009-UseMultipleTranslationFilesByDefaultInEXTform.html
 */
final class TranslationFileRector implements \Ssch\TYPO3Rector\Contract\FileProcessor\Yaml\Form\FormYamlRectorInterface
{
    /**
     * @var string
     */
    private const TRANSLATION_FILE_KEY = 'translationFile';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use key translationFiles instead of translationFile', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
TYPO3:
  CMS:
    Form:
      prototypes:
        standard:
          formElementsDefinition:
            Form:
              renderingOptions:
                translation:
                  translationFile:
                    10: 'EXT:form/Resources/Private/Language/locallang.xlf'
                    20: 'EXT:myextension/Resources/Private/Language/locallang.xlf'
CODE_SAMPLE
, <<<'CODE_SAMPLE'
TYPO3:
  CMS:
    Form:
      prototypes:
        standard:
          formElementsDefinition:
            Form:
              renderingOptions:
                translation:
                  translationFiles:
                    20: 'EXT:myextension/Resources/Private/Language/locallang.xlf'
CODE_SAMPLE
)]);
    }
    public function refactor(array $yaml) : array
    {
        return $this->refactorTranslationFile($yaml);
    }
    /**
     * @param mixed[] $yaml
     *
     * @return mixed[]
     */
    private function refactorTranslationFile(array &$yaml) : array
    {
        foreach ($yaml as &$section) {
            if (\is_array($section)) {
                if (\array_key_exists(self::TRANSLATION_FILE_KEY, $section) && \is_array($section[self::TRANSLATION_FILE_KEY])) {
                    $section['translationFiles'] = $this->buildNewTranslations($section[self::TRANSLATION_FILE_KEY]);
                    unset($section[self::TRANSLATION_FILE_KEY]);
                }
                $this->refactorTranslationFile($section);
            }
        }
        unset($section);
        return $yaml;
    }
    /**
     * @param array<int, string> $oldTranslations
     *
     * @return array<int, string>
     */
    private function buildNewTranslations(array $oldTranslations) : array
    {
        $newTranslations = [];
        foreach ($oldTranslations as $oldTranslationFileKey => $oldTranslationFile) {
            if (\strncmp($oldTranslationFile, 'EXT:form', \strlen('EXT:form')) !== 0) {
                $newTranslations[$oldTranslationFileKey] = $oldTranslationFile;
            }
        }
        return $newTranslations;
    }
}

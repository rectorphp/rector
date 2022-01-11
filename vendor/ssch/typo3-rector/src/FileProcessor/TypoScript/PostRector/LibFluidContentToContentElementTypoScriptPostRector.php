<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\PostRector;

use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptPostRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class LibFluidContentToContentElementTypoScriptPostRector implements \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptPostRectorInterface
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert lib.fluidContent to lib.contentElement', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
lib.fluidContent.templateRootPaths.200 = EXT:your_extension_key/Resources/Private/Templates/
CODE_SAMPLE
, <<<'CODE_SAMPLE'
lib.contentElement.templateRootPaths.200 = EXT:your_extension_key/Resources/Private/Templates/
CODE_SAMPLE
)]);
    }
    public function apply(string $typoScriptContent) : string
    {
        return \str_replace('lib.fluidContent', 'lib.contentElement', $typoScriptContent);
    }
}

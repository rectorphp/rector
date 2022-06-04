<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\v8\v7;

use RectorPrefix20220604\Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use RectorPrefix20220604\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/8.7/Breaking-80412-NewSharedContentElementTyposcriptLibraryObjectForFluidStyledContent.html
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\TypoScript\TypoScriptProcessorTest
 */
final class LibFluidContentToLibContentElementRector extends \Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector
{
    public function enterNode(\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
        if (!$statement instanceof \RectorPrefix20220604\Helmich\TypoScriptParser\Parser\AST\NestedAssignment && !$statement instanceof \RectorPrefix20220604\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment) {
            return;
        }
        if ('lib.fluidContent' === $statement->object->relativeName) {
            $this->hasChanged = \true;
            $statement->object->relativeName = 'lib.contentElement';
            return;
        }
        if ('fluidContent' === $statement->object->relativeName) {
            $this->hasChanged = \true;
            $statement->object->relativeName = 'contentElement';
        }
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert lib.fluidContent to lib.contentElement', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
lib.fluidContent {
   templateRootPaths {
      200 = EXT:your_extension_key/Resources/Private/Templates/
   }
   partialRootPaths {
      200 = EXT:your_extension_key/Resources/Private/Partials/
   }
   layoutRootPaths {
      200 = EXT:your_extension_key/Resources/Private/Layouts/
   }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
lib.contentElement {
   templateRootPaths {
      200 = EXT:your_extension_key/Resources/Private/Templates/
   }
   partialRootPaths {
      200 = EXT:your_extension_key/Resources/Private/Partials/
   }
   layoutRootPaths {
      200 = EXT:your_extension_key/Resources/Private/Layouts/
   }
}
CODE_SAMPLE
)]);
    }
}

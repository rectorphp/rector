<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\FlexForms\Rector;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Ssch\TYPO3Rector\Contract\FileProcessor\FlexForms\Rector\FlexFormRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class RenderTypeFlexFormRector implements \Ssch\TYPO3Rector\Contract\FileProcessor\FlexForms\Rector\FlexFormRectorInterface
{
    public function transform(\DOMDocument $domDocument) : bool
    {
        $xpath = new \DOMXPath($domDocument);
        /** @var DOMNodeList<DOMElement> $elements */
        $elements = $xpath->query('//TCEforms/config');
        $hasChanged = \false;
        foreach ($elements as $element) {
            $types = $element->getElementsByTagName('type');
            $type = $types->item(0);
            if (null === $type) {
                continue;
            }
            if ('select' !== $type->textContent) {
                continue;
            }
            $renderTypes = $element->getElementsByTagName('renderType');
            $renderType = $renderTypes->item(0);
            if (null !== $renderType) {
                continue;
            }
            $renderModes = $element->getElementsByTagName('renderMode');
            $renderMode = $renderModes->item(0);
            $sizes = $element->getElementsByTagName('size');
            $size = $sizes->item(0);
            $renderTypeName = 'selectSingle';
            $insertBefore = $type;
            if ($renderMode instanceof \DOMNode) {
                $renderTypeName = 'selectTree';
                $insertBefore = $renderMode;
            } elseif ($size instanceof \DOMNode && (int) $size->textContent > 1) {
                // Could be also selectCheckBox. This is a sensitive default
                $renderTypeName = 'selectMultipleSideBySide';
            }
            $renderType = $domDocument->createElement('renderType', $renderTypeName);
            if (null === $insertBefore->parentNode) {
                continue;
            }
            if (null === $insertBefore->nextSibling) {
                continue;
            }
            $hasChanged = \true;
            $insertBefore->parentNode->insertBefore($renderType, $insertBefore->nextSibling);
            $insertBefore->parentNode->insertBefore($domDocument->createTextNode("\n"), $insertBefore->nextSibling);
        }
        return $hasChanged;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add renderType node in Flexforms xml', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
<type>select</type>
<items>
    <numIndex index="0" type="array">
        <numIndex index="0">
            LLL:EXT:news/Resources/Private/Language/locallang_be.xlf:flexforms_general.no-constraint
        </numIndex>
</items>
CODE_SAMPLE
, <<<'CODE_SAMPLE'
<type>select</type>
<renderType>selectSingle</renderType>
<items>
    <numIndex index="0" type="array">
        <numIndex index="0">
            LLL:EXT:news/Resources/Private/Language/locallang_be.xlf:flexforms_general.no-constraint
        </numIndex>
</items>
CODE_SAMPLE
)]);
    }
}

<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v7\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\ArrayUtility;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.4/Deprecation-67737-TcaDropAdditionalPalette.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v7\v4\DropAdditionalPaletteRector\DropAdditionalPaletteRectorTest
 */
final class DropAdditionalPaletteRector extends \Rector\Core\Rector\AbstractRector
{
    use TcaHelperTrait;
    /**
     * @var string
     */
    private const FIELD_NAME = 'fieldName';
    /**
     * @var string
     */
    private const FIELD_LABEL = 'fieldLabel';
    /**
     * @var string
     */
    private const PALETTE_NAME = 'paletteName';
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function refactor($node) : ?\PhpParser\Node
    {
        if (!$this->isFullTca($node)) {
            return null;
        }
        $types = $this->extractTypes($node);
        if (!$types instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        $typesItems = $types->value;
        if (!$typesItems instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($typesItems->items as $typesItem) {
            if (!$typesItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $typesItem->key) {
                continue;
            }
            if (!$typesItem->value instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            foreach ($typesItem->value->items as $typeItem) {
                if (!$typeItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                    continue;
                }
                if (null === $typeItem->key) {
                    continue;
                }
                if (!$this->valueResolver->isValue($typeItem->key, 'showitem')) {
                    continue;
                }
                $showItemValue = $this->valueResolver->getValue($typeItem->value);
                if (null === $showItemValue || !\is_string($showItemValue) || \false === \strpos($showItemValue, ';')) {
                    continue;
                }
                $itemList = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode(',', $showItemValue, \true);
                $newFieldStrings = [];
                foreach ($itemList as $fieldString) {
                    $fieldArray = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode(';', $fieldString);
                    $fieldArray = [self::FIELD_NAME => $fieldArray[0] ?? '', self::FIELD_LABEL => $fieldArray[1] ?? null, self::PALETTE_NAME => $fieldArray[2] ?? null];
                    if ('--palette--' !== $fieldArray[self::FIELD_NAME] && null !== $fieldArray[self::PALETTE_NAME]) {
                        if ($fieldArray[self::FIELD_LABEL]) {
                            $fieldString = $fieldArray[self::FIELD_NAME] . ';' . $fieldArray[self::FIELD_LABEL];
                        } else {
                            $fieldString = $fieldArray[self::FIELD_NAME];
                        }
                        $paletteString = '--palette--;;' . $fieldArray[self::PALETTE_NAME];
                        $newFieldStrings[] = $fieldString;
                        $newFieldStrings[] = $paletteString;
                    } else {
                        $newFieldStrings[] = $fieldString;
                    }
                }
                if ($newFieldStrings === $itemList) {
                    // do not alter the syntax tree, if there are no changes. This will keep formatting of the code intact
                    continue;
                }
                $typeItem->value = new \PhpParser\Node\Scalar\String_(\implode(',', $newFieldStrings));
                $hasAstBeenChanged = \true;
            }
        }
        return $hasAstBeenChanged ? $node : null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('TCA: Drop additional palette', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return [
    'types' => [
        'aType' => [
            'showitem' => 'aField;aLabel;anAdditionalPaletteName',
        ],
     ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'types' => [
        'aType' => [
            'showitem' => 'aField;aLabel, --palette--;;anAdditionalPaletteName',
        ],
     ],
];
CODE_SAMPLE
)]);
    }
}

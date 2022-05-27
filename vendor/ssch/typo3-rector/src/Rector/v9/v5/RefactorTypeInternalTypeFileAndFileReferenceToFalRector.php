<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v5;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Console\Output\RectorOutputStyle;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Deprecation-86406-TCATypeGroupInternal_typeFileAndFile_reference.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v5\RefactorTypeInternalTypeFileAndFileReferenceToFalRector\RefactorTypeInternalTypeFileAndFileReferenceToFalRectorTest
 */
final class RefactorTypeInternalTypeFileAndFileReferenceToFalRector extends \Rector\Core\Rector\AbstractRector
{
    use TcaHelperTrait;
    /**
     * @var string
     */
    private const MESSAGE = 'You have to migrate the legacy file field to FAL';
    /**
     * @readonly
     * @var \Rector\Core\Console\Output\RectorOutputStyle
     */
    private $rectorOutputStyle;
    public function __construct(\Rector\Core\Console\Output\RectorOutputStyle $rectorOutputStyle)
    {
        $this->rectorOutputStyle = $rectorOutputStyle;
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
        $columnsArrayItem = $this->extractColumns($node);
        if (!$columnsArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        $columnItems = $columnsArrayItem->value;
        if (!$columnItems instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($columnItems->items as $columnItem) {
            if (!$columnItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $columnItem->key) {
                continue;
            }
            if (!$columnItem->value instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            foreach ($columnItem->value->items as $configValue) {
                if (null === $configValue) {
                    continue;
                }
                if (null === $configValue->key) {
                    continue;
                }
                if (!$configValue->value instanceof \PhpParser\Node\Expr\Array_) {
                    continue;
                }
                if (!$this->valueResolver->isValue($configValue->key, 'config')) {
                    continue;
                }
                if (!$this->isConfigType($configValue->value, 'group')) {
                    continue;
                }
                if (!$this->configIsOfInternalType($configValue->value, 'file') && !$this->configIsOfInternalType($configValue->value, 'file_reference')) {
                    continue;
                }
                $newConfig = new \PhpParser\Node\Expr\Array_();
                $allowed = null;
                foreach ($configValue->value->items as $configItemValue) {
                    if (!$configItemValue instanceof \PhpParser\Node\Expr\ArrayItem) {
                        continue;
                    }
                    if (null === $configItemValue->key) {
                        continue;
                    }
                    if ($this->valueResolver->isValues($configItemValue->key, ['max_size', 'uploadfolder', 'maxitems'])) {
                        $newConfig->items[] = new \PhpParser\Node\Expr\ArrayItem($configItemValue->value, $configItemValue->key);
                        continue;
                    }
                    if ($this->valueResolver->isValue($configItemValue->key, 'allowed')) {
                        $allowed = $configItemValue->value;
                    }
                }
                $hasAstBeenChanged = \true;
                $args = [$columnItem->key, $newConfig];
                if (null !== $allowed) {
                    $args[] = $allowed;
                }
                $configValue->value = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility', 'getFileFieldTCAConfig', $args);
            }
        }
        if ($hasAstBeenChanged) {
            $this->rectorOutputStyle->warning(self::MESSAGE);
        }
        return $hasAstBeenChanged ? $node : null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move TCA type group internal_type file and file_reference to FAL configuration', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return [
            'ctrl' => [],
            'columns' => [
                'foobar_image' => [
                    'exclude' => 1,
                    'label' => 'FoobarLabel',
                    'config' => [
                        'type' => 'group',
                        'internal_type' => 'file',
                        'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
                        'max_size' => '20000',
                        'uploadfolder' => 'fileadmin/foobar',
                        'maxitems' => '1',
                    ],
                ],
            ],
        ];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
            'ctrl' => [],
            'columns' => [
                'foobar_image' => [
                    'exclude' => 1,
                    'label' => 'FoobarLabel',
                    'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                        'foobar_image',
                        [
                            'max_size' => '20000',
                            'uploadfolder' => 'fileadmin/foobar',
                            'maxitems' => 1,
                            'appearance' => [
                                'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
                            ],
                        ],
                        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
                    ),
                ],
            ],
        ];
CODE_SAMPLE
)]);
    }
}

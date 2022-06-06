<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v6;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\ArrayUtility;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Deprecation-79440-TcaChanges.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v6\MigrateOptionsOfTypeGroupRector\MigrateOptionsOfTypeGroupRectorTest
 */
final class MigrateOptionsOfTypeGroupRector extends AbstractRector
{
    use TcaHelperTrait;
    /**
     * @var string
     */
    private const DISABLED = 'disabled';
    /**
     * @var array<string, array<string, mixed>>
     */
    private $addFieldWizards = [];
    /**
     * @var array<string, array<string, mixed>>
     */
    private $addFieldControls = [];
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isFullTca($node)) {
            return null;
        }
        $columnsArray = $this->extractSubArrayByKey($node->expr, 'columns');
        if (!$columnsArray instanceof Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($this->extractColumnConfig($columnsArray) as $config) {
            if (!$config instanceof Array_) {
                continue;
            }
            if (!$this->hasKeyValuePair($config, 'type', 'group')) {
                continue;
            }
            $this->addFieldWizards = [];
            $this->addFieldControls = [];
            $hasAstBeenChanged = $this->dropSelectedListType($config);
            $hasAstBeenChanged = $this->refactorShowThumbs($config) ? \true : $hasAstBeenChanged;
            $hasAstBeenChanged = $this->refactorDisableControls($config) ? \true : $hasAstBeenChanged;
            if ([] !== $this->addFieldControls) {
                $config->items[] = new ArrayItem($this->nodeFactory->createArray($this->addFieldControls), new String_('fieldControl'));
            }
            if ([] !== $this->addFieldWizards) {
                $config->items[] = new ArrayItem($this->nodeFactory->createArray($this->addFieldWizards), new String_('fieldWizard'));
            }
        }
        return $hasAstBeenChanged ? $node : null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate options if type group in TCA', [new CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [],
    'columns' => [
        'image2' => [
            'config' => [
                'selectedListStyle' => 'foo',
                'type' => 'group',
                'internal_type' => 'file',
                'show_thumbs' => '0',
                'disable_controls' => 'browser'
            ],
        ],
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'ctrl' => [],
    'columns' => [
        'image2' => [
            'config' => [
                'type' => 'group',
                'internal_type' => 'file',
                'fieldControl' => [
                    'elementBrowser' => ['disabled' => true]
                ],
                'fieldWizard' => [
                    'fileThumbnails' => ['disabled' => true]
                ]
            ],
        ],
    ],
];
CODE_SAMPLE
)]);
    }
    private function dropSelectedListType(Array_ $configArray) : bool
    {
        $listStyle = $this->extractArrayItemByKey($configArray, 'selectedListStyle');
        if (null !== $listStyle) {
            $this->removeNode($listStyle);
            return \true;
        }
        return \false;
    }
    private function refactorShowThumbs(Array_ $configArray) : bool
    {
        $hasAstBeenChanged = \false;
        $showThumbs = $this->extractArrayItemByKey($configArray, 'show_thumbs');
        if (null !== $showThumbs) {
            $this->removeNode($showThumbs);
            $hasAstBeenChanged = \true;
            if (!(bool) $this->getValue($showThumbs->value)) {
                if ($this->hasKeyValuePair($configArray, 'internal_type', 'db')) {
                    $this->addFieldWizards['recordsOverview'][self::DISABLED] = \true;
                } elseif ($this->hasKeyValuePair($configArray, 'internal_type', 'file')) {
                    $this->addFieldWizards['fileThumbnails'][self::DISABLED] = \true;
                }
            }
        }
        return $hasAstBeenChanged;
    }
    private function refactorDisableControls(Array_ $configArray) : bool
    {
        $hasAstBeenChanged = \false;
        $disableControls = $this->extractArrayItemByKey($configArray, 'disable_controls');
        if (null !== $disableControls) {
            $this->removeNode($disableControls);
            $hasAstBeenChanged = \true;
            if (\is_string($this->getValue($disableControls->value))) {
                $controls = ArrayUtility::trimExplode(',', $this->getValue($disableControls->value), \true);
                foreach ($controls as $control) {
                    if ('browser' === $control) {
                        $this->addFieldControls['elementBrowser'][self::DISABLED] = \true;
                    } elseif ('delete' === $control) {
                        $configArray->items[] = new ArrayItem($this->nodeFactory->createTrue(), new String_('hideDeleteIcon'));
                    } elseif ('allowedTables' === $control) {
                        $this->addFieldWizards['tableList'][self::DISABLED] = \true;
                    } elseif ('upload' === $control) {
                        $this->addFieldWizards['fileUpload'][self::DISABLED] = \true;
                    }
                }
            }
        }
        return $hasAstBeenChanged;
    }
}

<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v6;

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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Deprecation-79440-TcaChanges.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v6\MigrateOptionsOfTypeGroupRector\MigrateOptionsOfTypeGroupRectorTest
 */
final class MigrateOptionsOfTypeGroupRector extends \Rector\Core\Rector\AbstractRector
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
        $columns = $this->extractSubArrayByKey($node->expr, 'columns');
        if (!$columns instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($this->extractColumnConfig($columns) as $config) {
            if (!$config instanceof \PhpParser\Node\Expr\Array_) {
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
                $config->items[] = new \PhpParser\Node\Expr\ArrayItem($this->nodeFactory->createArray($this->addFieldControls), new \PhpParser\Node\Scalar\String_('fieldControl'));
            }
            if ([] !== $this->addFieldWizards) {
                $config->items[] = new \PhpParser\Node\Expr\ArrayItem($this->nodeFactory->createArray($this->addFieldWizards), new \PhpParser\Node\Scalar\String_('fieldWizard'));
            }
        }
        return $hasAstBeenChanged ? $node : null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate options if type group in TCA', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    private function dropSelectedListType(\PhpParser\Node\Expr\Array_ $config) : bool
    {
        $listStyle = $this->extractArrayItemByKey($config, 'selectedListStyle');
        if (null !== $listStyle) {
            $this->removeNode($listStyle);
            return \true;
        }
        return \false;
    }
    private function refactorShowThumbs(\PhpParser\Node\Expr\Array_ $config) : bool
    {
        $hasAstBeenChanged = \false;
        $showThumbs = $this->extractArrayItemByKey($config, 'show_thumbs');
        if (null !== $showThumbs) {
            $this->removeNode($showThumbs);
            $hasAstBeenChanged = \true;
            if (!(bool) $this->getValue($showThumbs->value)) {
                if ($this->hasKeyValuePair($config, 'internal_type', 'db')) {
                    $this->addFieldWizards['recordsOverview'][self::DISABLED] = \true;
                } elseif ($this->hasKeyValuePair($config, 'internal_type', 'file')) {
                    $this->addFieldWizards['fileThumbnails'][self::DISABLED] = \true;
                }
            }
        }
        return $hasAstBeenChanged;
    }
    private function refactorDisableControls(\PhpParser\Node\Expr\Array_ $config) : bool
    {
        $hasAstBeenChanged = \false;
        $disableControls = $this->extractArrayItemByKey($config, 'disable_controls');
        if (null !== $disableControls) {
            $this->removeNode($disableControls);
            $hasAstBeenChanged = \true;
            if (\is_string($this->getValue($disableControls->value))) {
                $controls = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode(',', $this->getValue($disableControls->value), \true);
                foreach ($controls as $control) {
                    if ('browser' === $control) {
                        $this->addFieldControls['elementBrowser'][self::DISABLED] = \true;
                    } elseif ('delete' === $control) {
                        $config->items[] = new \PhpParser\Node\Expr\ArrayItem($this->nodeFactory->createTrue(), new \PhpParser\Node\Scalar\String_('hideDeleteIcon'));
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

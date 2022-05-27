<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v2;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\FilesFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.2/Deprecation-93944-FileTreeAsIframeMigratedToSVG-basedTree.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v2\MigrateFrameModuleToSvgTreeRector\MigrateFrameModuleToSvgTreeRectorTest
 */
final class MigrateFrameModuleToSvgTreeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\FilesFinder
     */
    private $filesFinder;
    public function __construct(FilesFinder $filesFinder)
    {
        $this->filesFinder = $filesFinder;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isNames($node->name, ['addModule', 'registerModule'])) {
            return null;
        }
        $hasAstBeenChanged = \false;
        if ($this->isName($node->name, 'addModule')) {
            $moduleConfig = $node->args[4]->value;
            if (!$moduleConfig instanceof Array_) {
                return null;
            }
            $hasAstBeenChanged = $this->migrateNavigationFrameModule($moduleConfig);
        }
        if ($this->isName($node->name, 'registerModule')) {
            $moduleConfig = $node->args[5]->value;
            if (!$moduleConfig instanceof Array_) {
                return null;
            }
            $hasAstBeenChanged = $this->migrateNavigationFrameModule($moduleConfig);
        }
        return $hasAstBeenChanged ? $node : null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate the iframe based file tree to SVG', [new CodeSample(<<<'CODE_SAMPLE'
'navigationFrameModule' => 'file_navframe'
CODE_SAMPLE
, <<<'CODE_SAMPLE'
'navigationComponentId' => 'TYPO3/CMS/Backend/Tree/FileStorageTreeContainer'
CODE_SAMPLE
)]);
    }
    private function shouldSkip(Node $node) : bool
    {
        $fileInfo = $this->file->getSmartFileInfo();
        if (!$this->filesFinder->isExtTables($fileInfo)) {
            return \true;
        }
        return !$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Extbase\\Utility\\ExtensionUtility')) && !$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility'));
    }
    private function migrateNavigationFrameModule(Array_ $moduleConfigArray) : bool
    {
        foreach ($moduleConfigArray->items as $item) {
            if (null === $item) {
                continue;
            }
            if (null === $item->key) {
                continue;
            }
            if (!$this->valueResolver->isValue($item->key, 'navigationFrameModule')) {
                continue;
            }
            if (!$this->valueResolver->isValue($item->value, 'file_navframe')) {
                continue;
            }
            $item->key = new String_('navigationComponentId');
            $item->value = new String_('TYPO3/CMS/Backend/Tree/FileStorageTreeContainer');
            return \true;
        }
        return \false;
    }
}

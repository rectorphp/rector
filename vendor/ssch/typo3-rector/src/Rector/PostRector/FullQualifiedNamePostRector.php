<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\PostRector;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\Core\Configuration\Option;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeRemoval\NodeRemover;
use Rector\PostRector\Rector\AbstractPostRector;
use Rector\PostRector\Rector\NameImportingPostRector;
use Ssch\TYPO3Rector\Configuration\Typo3Option;
use RectorPrefix20220501\Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Symplify\Skipper\Matcher\FileInfoMatcher;
/**
 * @see \Ssch\TYPO3Rector\Tests\Rector\PostRector\FullQualifiedNamePostRector\FullQualifiedNamePostRectorTest
 */
final class FullQualifiedNamePostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper
     */
    private $classNameImportSkipper;
    /**
     * @readonly
     * @var \Symplify\Skipper\Matcher\FileInfoMatcher
     */
    private $fileInfoMatcher;
    public function __construct(\RectorPrefix20220501\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeRemoval\NodeRemover $nodeRemover, \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper $classNameImportSkipper, \RectorPrefix20220501\Symplify\Skipper\Matcher\FileInfoMatcher $fileInfoMatcher)
    {
        $this->parameterProvider = $parameterProvider;
        $this->currentFileProvider = $currentFileProvider;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeRemover = $nodeRemover;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->fileInfoMatcher = $fileInfoMatcher;
        $this->changeNameImportingPostRectorSkipConfiguration($this->parameterProvider);
    }
    public function enterNode(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof \Rector\Core\ValueObject\Application\File) {
            return null;
        }
        if ($this->shouldSkip($file)) {
            return null;
        }
        if (!$node instanceof \PhpParser\Node\Name) {
            return null;
        }
        return $this->processNodeName($node, $file);
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use fully qualified names', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use \TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::configurePlugin(
        'News',
        'Pi1',
        [
            \GeorgRinger\News\Controller\NewsController::class => 'list,detail,selectedList,dateMenu,searchForm,searchResult',
            \GeorgRinger\News\Controller\CategoryController::class => 'list',
            \GeorgRinger\News\Controller\TagController::class => 'list',
        ],
        [
            'News' => 'searchForm,searchResult',
        ]
    );
CODE_SAMPLE
, <<<'CODE_SAMPLE'
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'News',
        'Pi1',
        [
            \GeorgRinger\News\Controller\NewsController::class => 'list,detail,selectedList,dateMenu,searchForm,searchResult',
            \GeorgRinger\News\Controller\CategoryController::class => 'list',
            \GeorgRinger\News\Controller\TagController::class => 'list',
        ],
        [
            'News' => 'searchForm,searchResult',
        ]
    );
CODE_SAMPLE
)]);
    }
    public function getPriority() : int
    {
        return 880;
    }
    private function processNodeName(\PhpParser\Node\Name $name, \Rector\Core\ValueObject\Application\File $file) : \PhpParser\Node
    {
        if ($name->isSpecialClassName()) {
            return $name;
        }
        /** @var Use_[] $currentUses */
        $currentUses = $this->betterNodeFinder->findInstanceOf($file->getNewStmts(), \PhpParser\Node\Stmt\Use_::class);
        if (!$this->shouldApplyFullQualifiedNamespace($name, $currentUses)) {
            return $name;
        }
        foreach ($currentUses as $currentUse) {
            $this->nodeRemover->removeNode($currentUse);
        }
        return new \PhpParser\Node\Name\FullyQualified($name);
    }
    /**
     * @param Use_[] $currentUses
     */
    private function shouldApplyFullQualifiedNamespace(\PhpParser\Node\Name $name, array $currentUses) : bool
    {
        if ($this->classNameImportSkipper->isFoundInUse($name, $currentUses)) {
            return \true;
        }
        return $this->classNameImportSkipper->isAlreadyImported($name, $currentUses);
    }
    private function shouldSkip(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        if (!$this->parameterProvider->hasParameter(\Ssch\TYPO3Rector\Configuration\Typo3Option::PATHS_FULL_QUALIFIED_NAMESPACES)) {
            return \true;
        }
        $filesAndDirectories = $this->parameterProvider->provideArrayParameter(\Ssch\TYPO3Rector\Configuration\Typo3Option::PATHS_FULL_QUALIFIED_NAMESPACES);
        return !$this->fileInfoMatcher->doesFileInfoMatchPatterns($file->getSmartFileInfo(), $filesAndDirectories);
    }
    private function changeNameImportingPostRectorSkipConfiguration(\RectorPrefix20220501\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider) : void
    {
        if (!$parameterProvider->hasParameter(\Ssch\TYPO3Rector\Configuration\Typo3Option::PATHS_FULL_QUALIFIED_NAMESPACES)) {
            return;
        }
        $useFullQualifiedClassNamePaths = $parameterProvider->provideArrayParameter(\Ssch\TYPO3Rector\Configuration\Typo3Option::PATHS_FULL_QUALIFIED_NAMESPACES);
        if ([] === $useFullQualifiedClassNamePaths) {
            return;
        }
        $skipConfiguration = $parameterProvider->hasParameter(\Rector\Core\Configuration\Option::SKIP) ? $parameterProvider->provideArrayParameter(\Rector\Core\Configuration\Option::SKIP) : [];
        $nameImportingPostRectorConfiguration = \array_search(\Rector\PostRector\Rector\NameImportingPostRector::class, $skipConfiguration, \true);
        // Do nothing because NameImportingPostRector is skipped totally
        if (\false !== $nameImportingPostRectorConfiguration && !\is_array($skipConfiguration[$nameImportingPostRectorConfiguration])) {
            return;
        }
        if (!\array_key_exists(\Rector\PostRector\Rector\NameImportingPostRector::class, $skipConfiguration)) {
            $skipConfiguration[\Rector\PostRector\Rector\NameImportingPostRector::class] = [];
        }
        $skipConfiguration[\Rector\PostRector\Rector\NameImportingPostRector::class] = \array_merge($skipConfiguration[\Rector\PostRector\Rector\NameImportingPostRector::class], $useFullQualifiedClassNamePaths);
        $parameterProvider->changeParameter(\Rector\Core\Configuration\Option::SKIP, $skipConfiguration);
    }
}

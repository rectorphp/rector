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
use RectorPrefix20220527\Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220527\Symplify\Skipper\Matcher\FileInfoMatcher;
/**
 * @see \Ssch\TYPO3Rector\Tests\Rector\PostRector\FullQualifiedNamePostRector\FullQualifiedNamePostRectorTest
 */
final class FullQualifiedNamePostRector extends AbstractPostRector
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
    public function __construct(ParameterProvider $parameterProvider, CurrentFileProvider $currentFileProvider, BetterNodeFinder $betterNodeFinder, NodeRemover $nodeRemover, ClassNameImportSkipper $classNameImportSkipper, FileInfoMatcher $fileInfoMatcher)
    {
        $this->parameterProvider = $parameterProvider;
        $this->currentFileProvider = $currentFileProvider;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeRemover = $nodeRemover;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->fileInfoMatcher = $fileInfoMatcher;
        $this->changeNameImportingPostRectorSkipConfiguration($this->parameterProvider);
    }
    public function enterNode(Node $node) : ?Node
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return null;
        }
        if ($this->shouldSkip($file)) {
            return null;
        }
        if (!$node instanceof Name) {
            return null;
        }
        return $this->processNodeName($node, $file);
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use fully qualified names', [new CodeSample(<<<'CODE_SAMPLE'
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
    private function processNodeName(Name $name, File $file) : Node
    {
        if ($name->isSpecialClassName()) {
            return $name;
        }
        /** @var Use_[] $currentUses */
        $currentUses = $this->betterNodeFinder->findInstanceOf($file->getNewStmts(), Use_::class);
        if (!$this->shouldApplyFullQualifiedNamespace($name, $currentUses)) {
            return $name;
        }
        foreach ($currentUses as $currentUse) {
            $this->nodeRemover->removeNode($currentUse);
        }
        return new FullyQualified($name);
    }
    /**
     * @param Use_[] $currentUses
     */
    private function shouldApplyFullQualifiedNamespace(Name $name, array $currentUses) : bool
    {
        if ($this->classNameImportSkipper->isFoundInUse($name, $currentUses)) {
            return \true;
        }
        return $this->classNameImportSkipper->isAlreadyImported($name, $currentUses);
    }
    private function shouldSkip(File $file) : bool
    {
        if (!$this->parameterProvider->hasParameter(Typo3Option::PATHS_FULL_QUALIFIED_NAMESPACES)) {
            return \true;
        }
        $filesAndDirectories = $this->parameterProvider->provideArrayParameter(Typo3Option::PATHS_FULL_QUALIFIED_NAMESPACES);
        return !$this->fileInfoMatcher->doesFileInfoMatchPatterns($file->getSmartFileInfo(), $filesAndDirectories);
    }
    private function changeNameImportingPostRectorSkipConfiguration(ParameterProvider $parameterProvider) : void
    {
        if (!$parameterProvider->hasParameter(Typo3Option::PATHS_FULL_QUALIFIED_NAMESPACES)) {
            return;
        }
        $useFullQualifiedClassNamePaths = $parameterProvider->provideArrayParameter(Typo3Option::PATHS_FULL_QUALIFIED_NAMESPACES);
        if ([] === $useFullQualifiedClassNamePaths) {
            return;
        }
        $skipConfiguration = $parameterProvider->hasParameter(Option::SKIP) ? $parameterProvider->provideArrayParameter(Option::SKIP) : [];
        $nameImportingPostRectorConfiguration = \array_search(NameImportingPostRector::class, $skipConfiguration, \true);
        // Do nothing because NameImportingPostRector is skipped totally
        if (\false !== $nameImportingPostRectorConfiguration && !\is_array($skipConfiguration[$nameImportingPostRectorConfiguration])) {
            return;
        }
        if (!\array_key_exists(NameImportingPostRector::class, $skipConfiguration)) {
            $skipConfiguration[NameImportingPostRector::class] = [];
        }
        $skipConfiguration[NameImportingPostRector::class] = \array_merge($skipConfiguration[NameImportingPostRector::class], $useFullQualifiedClassNamePaths);
        $parameterProvider->changeParameter(Option::SKIP, $skipConfiguration);
    }
}

<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use RectorPrefix20220501\Nette\Utils\Json;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Ssch\TYPO3Rector\Helper\FilesFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Important-82692-GuidelinesForExtensionFiles.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\ReplaceExtKeyWithExtensionKeyRector\ReplaceExtKeyWithExtensionKeyFromFolderNameTest
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\ReplaceExtKeyWithExtensionKeyRector\ReplaceExtKeyWithExtensionKeyFromComposerJsonNameRectorTest
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\ReplaceExtKeyWithExtensionKeyRector\ReplaceExtKeyWithExtensionKeyFromComposerJsonExtensionKeyExtraSectionRectorTest
 */
final class ReplaceExtKeyWithExtensionKeyRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\FilesFinder
     */
    private $filesFinder;
    public function __construct(\Ssch\TYPO3Rector\Helper\FilesFinder $filesFinder)
    {
        $this->filesFinder = $filesFinder;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace $_EXTKEY with extension key', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
ExtensionUtility::configurePlugin(
    'Foo.'.$_EXTKEY,
    'ArticleTeaser',
    [
        'FooBar' => 'baz',
    ]
);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
ExtensionUtility::configurePlugin(
    'Foo.'.'bar',
    'ArticleTeaser',
    [
        'FooBar' => 'baz',
    ]
);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Variable::class];
    }
    /**
     * @param Variable $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $fileInfo = $this->file->getSmartFileInfo();
        if ($this->filesFinder->isExtEmconf($fileInfo)) {
            return null;
        }
        if (!$this->isExtensionKeyVariable($node)) {
            return null;
        }
        $extEmConf = $this->createExtensionKeyFromFolder($fileInfo);
        if (!$extEmConf instanceof \Symplify\SmartFileSystem\SmartFileInfo) {
            return null;
        }
        if ($this->isAssignment($node)) {
            return null;
        }
        $extensionKey = $this->resolveExtensionKeyByComposerJson($extEmConf);
        if (null === $extensionKey) {
            $extensionKey = \basename($extEmConf->getRealPathDirectory());
        }
        return new \PhpParser\Node\Scalar\String_($extensionKey);
    }
    private function isExtensionKeyVariable(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        return $this->isName($variable, '_EXTKEY');
    }
    private function createExtensionKeyFromFolder(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : ?\Symplify\SmartFileSystem\SmartFileInfo
    {
        return $this->filesFinder->findExtEmConfRelativeFromGivenFileInfo($fileInfo);
    }
    private function isAssignment(\PhpParser\Node\Expr\Variable $node) : bool
    {
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // Check if we have an assigment to the property, if so do not change it
        return $parentNode instanceof \PhpParser\Node\Expr\Assign && $parentNode->var === $node;
    }
    private function resolveExtensionKeyByComposerJson(\Symplify\SmartFileSystem\SmartFileInfo $extEmConf) : ?string
    {
        try {
            $composerJson = new \Symplify\SmartFileSystem\SmartFileInfo($extEmConf->getRealPathDirectory() . '/composer.json');
            $json = \RectorPrefix20220501\Nette\Utils\Json::decode($composerJson->getContents(), \RectorPrefix20220501\Nette\Utils\Json::FORCE_ARRAY);
            if (isset($json['extra']['typo3/cms']['extension-key'])) {
                return $json['extra']['typo3/cms']['extension-key'];
            }
            if (isset($json['name'])) {
                [, $extensionKey] = \explode('/', (string) $json['name'], 2);
                return \str_replace('-', '_', $extensionKey);
            }
        } catch (\RectorPrefix20220501\Symplify\SmartFileSystem\Exception\FileNotFoundException $exception) {
            return null;
        }
        return null;
    }
}

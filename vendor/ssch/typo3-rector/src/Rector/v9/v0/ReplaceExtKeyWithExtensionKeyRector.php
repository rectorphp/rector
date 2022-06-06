<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v0;

use RectorPrefix20220606\Nette\Utils\Json;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\FilesFinder;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220606\Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Important-82692-GuidelinesForExtensionFiles.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\ReplaceExtKeyWithExtensionKeyRector\ReplaceExtKeyWithExtensionKeyFromFolderNameTest
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\ReplaceExtKeyWithExtensionKeyRector\ReplaceExtKeyWithExtensionKeyFromComposerJsonNameRectorTest
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\ReplaceExtKeyWithExtensionKeyRector\ReplaceExtKeyWithExtensionKeyFromComposerJsonExtensionKeyExtraSectionRectorTest
 */
final class ReplaceExtKeyWithExtensionKeyRector extends AbstractRector
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
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace $_EXTKEY with extension key', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Variable::class];
    }
    /**
     * @param Variable $node
     */
    public function refactor(Node $node) : ?Node
    {
        $fileInfo = $this->file->getSmartFileInfo();
        if ($this->filesFinder->isExtEmconf($fileInfo)) {
            return null;
        }
        if (!$this->isExtensionKeyVariable($node)) {
            return null;
        }
        $extEmConf = $this->createExtensionKeyFromFolder($fileInfo);
        if (!$extEmConf instanceof SmartFileInfo) {
            return null;
        }
        if ($this->isAssignment($node)) {
            return null;
        }
        $extensionKey = $this->resolveExtensionKeyByComposerJson($extEmConf);
        if (null === $extensionKey) {
            $extensionKey = \basename($extEmConf->getRealPathDirectory());
        }
        return new String_($extensionKey);
    }
    private function isExtensionKeyVariable(Variable $variable) : bool
    {
        return $this->isName($variable, '_EXTKEY');
    }
    private function createExtensionKeyFromFolder(SmartFileInfo $fileInfo) : ?SmartFileInfo
    {
        return $this->filesFinder->findExtEmConfRelativeFromGivenFileInfo($fileInfo);
    }
    private function isAssignment(Variable $node) : bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        // Check if we have an assigment to the property, if so do not change it
        return $parentNode instanceof Assign && $parentNode->var === $node;
    }
    private function resolveExtensionKeyByComposerJson(SmartFileInfo $extEmConf) : ?string
    {
        try {
            $composerJson = new SmartFileInfo($extEmConf->getRealPathDirectory() . '/composer.json');
            $json = Json::decode($composerJson->getContents(), Json::FORCE_ARRAY);
            if (isset($json['extra']['typo3/cms']['extension-key'])) {
                return $json['extra']['typo3/cms']['extension-key'];
            }
            if (isset($json['name'])) {
                [, $extensionKey] = \explode('/', (string) $json['name'], 2);
                return \str_replace('-', '_', $extensionKey);
            }
        } catch (FileNotFoundException $exception) {
            return null;
        }
        return null;
    }
}

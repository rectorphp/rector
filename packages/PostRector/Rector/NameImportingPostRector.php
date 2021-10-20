<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Core\Configuration\Option;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNameImporter;
use RectorPrefix20211020\Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class NameImportingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var \Rector\CodingStyle\Node\NameImporter
     */
    private $nameImporter;
    /**
     * @var \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNameImporter
     */
    private $docBlockNameImporter;
    /**
     * @var \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper
     */
    private $classNameImportSkipper;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\RectorPrefix20211020\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \Rector\CodingStyle\Node\NameImporter $nameImporter, \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNameImporter $docBlockNameImporter, \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper $classNameImportSkipper, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->parameterProvider = $parameterProvider;
        $this->nameImporter = $nameImporter;
        $this->docBlockNameImporter = $docBlockNameImporter;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->currentFileProvider = $currentFileProvider;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function enterNode(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES)) {
            return null;
        }
        $file = $this->currentFileProvider->getFile();
        if ($node instanceof \PhpParser\Node\Name) {
            if (!$file instanceof \Rector\Core\ValueObject\Application\File) {
                return null;
            }
            if (!$this->shouldApply($file)) {
                return null;
            }
            return $this->processNodeName($node, $file);
        }
        if (!$this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::IMPORT_DOC_BLOCKS)) {
            return null;
        }
        if ($file instanceof \Rector\Core\ValueObject\Application\File && !$this->shouldApply($file)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->docBlockNameImporter->importNames($phpDocInfo->getPhpDocNode(), $node);
        return $node;
    }
    public function getPriority() : int
    {
        // this must run after NodeRemovingPostRector, sine renamed use imports can block next import
        return 600;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Imports fully qualified names', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(App\AnotherClass $anotherClass)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use App\AnotherClass;

class SomeClass
{
    public function run(AnotherClass $anotherClass)
    {
    }
}
CODE_SAMPLE
)]);
    }
    private function processNodeName(\PhpParser\Node\Name $name, \Rector\Core\ValueObject\Application\File $file) : ?\PhpParser\Node
    {
        if ($name->isSpecialClassName()) {
            return $name;
        }
        // @todo test if old stmts or new stmts! or both? :)
        /** @var Use_[] $currentUses */
        $currentUses = $this->betterNodeFinder->findInstanceOf($file->getNewStmts(), \PhpParser\Node\Stmt\Use_::class);
        if ($this->shouldImportName($name, $file, $currentUses)) {
            return $this->nameImporter->importName($name, $file, $currentUses);
        }
        return null;
    }
    /**
     * @param Use_[] $currentUses
     */
    private function shouldImportName(\PhpParser\Node\Name $name, \Rector\Core\ValueObject\Application\File $file, array $currentUses) : bool
    {
        if (\substr_count($name->toCodeString(), '\\') <= 1) {
            return \true;
        }
        if (!$this->classNameImportSkipper->isFoundInUse($name, $currentUses)) {
            return \true;
        }
        if ($this->classNameImportSkipper->isAlreadyImported($name, $currentUses)) {
            return \true;
        }
        return $this->reflectionProvider->hasFunction(new \PhpParser\Node\Name($name->getLast()), null);
    }
    private function shouldApply(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        if (!$this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::APPLY_AUTO_IMPORT_NAMES_ON_CHANGED_FILES_ONLY)) {
            return \true;
        }
        return $file->hasContentChanged();
    }
}

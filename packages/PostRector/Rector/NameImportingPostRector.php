<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Naming\Naming\AliasNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNameImporter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class NameImportingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\CodingStyle\Node\NameImporter
     */
    private $nameImporter;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNameImporter
     */
    private $docBlockNameImporter;
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper
     */
    private $classNameImportSkipper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
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
     * @var \Rector\Naming\Naming\AliasNameResolver
     */
    private $aliasNameResolver;
    public function __construct(ParameterProvider $parameterProvider, NameImporter $nameImporter, DocBlockNameImporter $docBlockNameImporter, ClassNameImportSkipper $classNameImportSkipper, PhpDocInfoFactory $phpDocInfoFactory, ReflectionProvider $reflectionProvider, CurrentFileProvider $currentFileProvider, BetterNodeFinder $betterNodeFinder, AliasNameResolver $aliasNameResolver)
    {
        $this->parameterProvider = $parameterProvider;
        $this->nameImporter = $nameImporter;
        $this->docBlockNameImporter = $docBlockNameImporter;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->currentFileProvider = $currentFileProvider;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->aliasNameResolver = $aliasNameResolver;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$this->parameterProvider->provideBoolParameter(Option::AUTO_IMPORT_NAMES)) {
            return null;
        }
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return null;
        }
        if (!$this->shouldApply($file)) {
            return null;
        }
        if ($node instanceof Name) {
            return $this->processNodeName($node, $file);
        }
        if ($this->parameterProvider->provideBoolParameter(Option::AUTO_IMPORT_DOC_BLOCK_NAMES)) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
            $this->docBlockNameImporter->importNames($phpDocInfo->getPhpDocNode(), $node);
        }
        return $node;
    }
    public function getPriority() : int
    {
        // this must run after NodeRemovingPostRector, sine renamed use imports can block next import
        return 600;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Imports fully qualified names', [new CodeSample(<<<'CODE_SAMPLE'
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
    private function processNodeName(Name $name, File $file) : ?Node
    {
        if ($name->isSpecialClassName()) {
            return $name;
        }
        // @todo test if old stmts or new stmts! or both? :)
        /** @var Use_[] $currentUses */
        $currentUses = $this->betterNodeFinder->findInstanceOf($file->getNewStmts(), Use_::class);
        if ($this->shouldImportName($name, $currentUses)) {
            $aliasName = $this->aliasNameResolver->resolveByName($name);
            $node = $name->getAttribute(AttributeKey::PARENT_NODE);
            if (\is_string($aliasName) && !$node instanceof UseUse && !$this->hasOtherSameUseStatementWithoutAlias($name, $currentUses)) {
                return new Name($aliasName);
            }
            return $this->nameImporter->importName($name, $file, $currentUses);
        }
        return null;
    }
    /**
     * @param Use_[] $currentUses
     */
    private function hasOtherSameUseStatementWithoutAlias(Name $name, array $currentUses) : bool
    {
        $currentName = $name->toString();
        foreach ($currentUses as $currentUse) {
            foreach ($currentUse->uses as $useUse) {
                if ($useUse->alias instanceof Identifier) {
                    continue;
                }
                if ($useUse->name->toString() === $currentName) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * @param Use_[] $currentUses
     */
    private function shouldImportName(Name $name, array $currentUses) : bool
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
        return $this->reflectionProvider->hasFunction(new Name($name->getLast()), null);
    }
    private function shouldApply(File $file) : bool
    {
        if (!$this->parameterProvider->provideBoolParameter(Option::APPLY_AUTO_IMPORT_NAMES_ON_CHANGED_FILES_ONLY)) {
            return \true;
        }
        return $file->hasContentChanged();
    }
}

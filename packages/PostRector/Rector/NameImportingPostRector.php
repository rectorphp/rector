<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Naming\Naming\AliasNameResolver;
use Rector\Naming\Naming\UseImportsResolver;
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
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\AliasNameResolver
     */
    private $aliasNameResolver;
    public function __construct(ParameterProvider $parameterProvider, NameImporter $nameImporter, DocBlockNameImporter $docBlockNameImporter, ClassNameImportSkipper $classNameImportSkipper, PhpDocInfoFactory $phpDocInfoFactory, ReflectionProvider $reflectionProvider, CurrentFileProvider $currentFileProvider, UseImportsResolver $useImportsResolver, AliasNameResolver $aliasNameResolver)
    {
        $this->parameterProvider = $parameterProvider;
        $this->nameImporter = $nameImporter;
        $this->docBlockNameImporter = $docBlockNameImporter;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->currentFileProvider = $currentFileProvider;
        $this->useImportsResolver = $useImportsResolver;
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
            return null;
        }
        $namespaces = \array_filter($file->getNewStmts(), static function (Stmt $stmt) : bool {
            return $stmt instanceof Namespace_;
        });
        if (\count($namespaces) > 1) {
            return null;
        }
        /** @var Use_[]|GroupUse[] $currentUses */
        $currentUses = $this->useImportsResolver->resolveForNode($name);
        if ($this->shouldImportName($name, $currentUses)) {
            $nameInUse = $this->resolveNameInUse($name, $currentUses);
            if ($nameInUse instanceof FullyQualified) {
                return null;
            }
            if ($nameInUse instanceof Name) {
                return $nameInUse;
            }
            return $this->nameImporter->importName($name, $file, $currentUses);
        }
        return null;
    }
    /**
     * @param Use_[]|GroupUse[] $currentUses
     * @return null|\PhpParser\Node\Name|\PhpParser\Node\Name\FullyQualified
     */
    private function resolveNameInUse(Name $name, array $currentUses)
    {
        $originalName = $name->getAttribute(AttributeKey::ORIGINAL_NAME);
        if (!$originalName instanceof FullyQualified) {
            return null;
        }
        $aliasName = $this->aliasNameResolver->resolveByName($name);
        if (\is_string($aliasName)) {
            return new Name($aliasName);
        }
        $isShortFullyQualifiedName = \substr_count($name->toCodeString(), '\\') === 1;
        if (!$isShortFullyQualifiedName) {
            return $this->resolveLongNameInUseName($name, $currentUses);
        }
        return $this->resolveConflictedShortNameInUse($name, $currentUses);
    }
    /**
     * @param Use_[]|GroupUse[] $currentUses
     */
    private function resolveLongNameInUseName(Name $name, array $currentUses) : ?Name
    {
        $lastName = $name->getLast();
        foreach ($currentUses as $currentUse) {
            foreach ($currentUse->uses as $useUse) {
                if ($useUse->name->getLast() !== $lastName) {
                    continue;
                }
                if ($useUse->alias instanceof Identifier && $useUse->alias->toString() !== $lastName) {
                    return new Name($lastName);
                }
            }
        }
        return null;
    }
    /**
     * @param Use_[]|GroupUse[] $currentUses
     */
    private function resolveConflictedShortNameInUse(Name $name, array $currentUses) : ?FullyQualified
    {
        $currentName = $name->toString();
        foreach ($currentUses as $currentUse) {
            $prefix = $this->useImportsResolver->resolvePrefix($currentUse);
            foreach ($currentUse->uses as $useUse) {
                $useName = $prefix . $name->toString();
                $lastUseName = $name->getLast();
                if (!$useUse->alias instanceof Identifier && $useName !== $currentName && $lastUseName === $currentName) {
                    return new FullyQualified($currentName);
                }
                if ($useUse->alias instanceof Identifier && $useUse->alias->toString() === $currentName) {
                    return new FullyQualified($currentName);
                }
            }
        }
        return null;
    }
    /**
     * @param Use_[]|GroupUse[] $currentUses
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

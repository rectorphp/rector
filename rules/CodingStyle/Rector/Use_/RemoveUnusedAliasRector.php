<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Use_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\CodingStyle\Naming\NameRenamer;
use Rector\CodingStyle\Node\DocAliasResolver;
use Rector\CodingStyle\Node\UseNameAliasToNameResolver;
use Rector\Core\PhpParser\NodeFinder\FullyQualifiedFromUseFinder;
use Rector\Core\Rector\AbstractRector;
use Rector\NameImporting\NodeAnalyzer\UseAnalyzer;
use Rector\NameImporting\ValueObject\NameAndParent;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\RemoveUnusedAliasRectorTest
 */
final class RemoveUnusedAliasRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, NameAndParent[]>
     */
    private $resolvedNamesAndParentsByShortName = [];
    /**
     * @var array<string, string[]>
     */
    private $useNamesAliasToName = [];
    /**
     * @var string[]
     */
    private $resolvedDocPossibleAliases = [];
    /**
     * @var \Rector\CodingStyle\Node\DocAliasResolver
     */
    private $docAliasResolver;
    /**
     * @var \Rector\NameImporting\NodeAnalyzer\UseAnalyzer
     */
    private $useAnalyzer;
    /**
     * @var \Rector\CodingStyle\Node\UseNameAliasToNameResolver
     */
    private $useNameAliasToNameResolver;
    /**
     * @var \Rector\CodingStyle\Naming\NameRenamer
     */
    private $nameRenamer;
    /**
     * @var \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper
     */
    private $classNameImportSkipper;
    /**
     * @var \Rector\Core\PhpParser\NodeFinder\FullyQualifiedFromUseFinder
     */
    private $fullyQualifiedFromUseFinder;
    public function __construct(\Rector\CodingStyle\Node\DocAliasResolver $docAliasResolver, \Rector\NameImporting\NodeAnalyzer\UseAnalyzer $useAnalyzer, \Rector\CodingStyle\Node\UseNameAliasToNameResolver $useNameAliasToNameResolver, \Rector\CodingStyle\Naming\NameRenamer $nameRenamer, \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper $classNameImportSkipper, \Rector\Core\PhpParser\NodeFinder\FullyQualifiedFromUseFinder $fullyQualifiedFromUseFinder)
    {
        $this->docAliasResolver = $docAliasResolver;
        $this->useAnalyzer = $useAnalyzer;
        $this->useNameAliasToNameResolver = $useNameAliasToNameResolver;
        $this->nameRenamer = $nameRenamer;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->fullyQualifiedFromUseFinder = $fullyQualifiedFromUseFinder;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes unused use aliases. Keep annotation aliases like "Doctrine\\ORM\\Mapping as ORM" to keep convention format', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Kernel as BaseKernel;

class SomeClass extends BaseKernel
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Kernel;

class SomeClass extends Kernel
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Use_::class];
    }
    /**
     * @param Use_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkipUse($node)) {
            return null;
        }
        $searchNode = $this->resolveSearchNode($node);
        if (!$searchNode instanceof \PhpParser\Node) {
            return null;
        }
        $this->resolvedNamesAndParentsByShortName = $this->useAnalyzer->resolveUsedNameNodes($searchNode);
        dump(\count($this->resolvedNamesAndParentsByShortName));
        die;
        $this->resolvedDocPossibleAliases = $this->docAliasResolver->resolve($searchNode);
        $this->useNamesAliasToName = $this->useNameAliasToNameResolver->resolve($this->file);
        // lowercase
        $this->resolvedDocPossibleAliases = $this->lowercaseArray($this->resolvedDocPossibleAliases);
        $this->resolvedNamesAndParentsByShortName = \array_change_key_case($this->resolvedNamesAndParentsByShortName, \CASE_LOWER);
        $this->useNamesAliasToName = \array_change_key_case($this->useNamesAliasToName, \CASE_LOWER);
        foreach ($node->uses as $use) {
            if ($use->alias === null) {
                continue;
            }
            $lastName = $use->name->getLast();
            $lowercasedLastName = \strtolower($lastName);
            /** @var string $aliasName */
            $aliasName = $this->getName($use->alias);
            if ($this->shouldSkip($node, $lastName, $aliasName)) {
                continue;
            }
            // only last name is used → no need for alias
            if (isset($this->resolvedNamesAndParentsByShortName[$lowercasedLastName])) {
                $use->alias = null;
                continue;
            }
            $this->refactorAliasName($node, $use->name->toString(), $lastName, $use);
        }
        return $node;
    }
    private function shouldSkipUse(\PhpParser\Node\Stmt\Use_ $use) : bool
    {
        // skip cases without namespace, problematic to analyse
        $namespace = $this->betterNodeFinder->findParentType($use, \PhpParser\Node\Stmt\Namespace_::class);
        if (!$namespace instanceof \PhpParser\Node) {
            return \true;
        }
        return !$this->hasUseAlias($use);
    }
    /**
     * @param string[] $values
     * @return string[]
     */
    private function lowercaseArray(array $values) : array
    {
        return \array_map('strtolower', $values);
    }
    private function shouldSkip(\PhpParser\Node\Stmt\Use_ $use, string $lastName, string $aliasName) : bool
    {
        // PHP is case insensitive
        $loweredLastName = \strtolower($lastName);
        $loweredAliasName = \strtolower($aliasName);
        // both are used → nothing to remove
        if (isset($this->resolvedNamesAndParentsByShortName[$loweredLastName], $this->resolvedNamesAndParentsByShortName[$loweredAliasName])) {
            return \true;
        }
        // part of some @Doc annotation
        if (\in_array($loweredAliasName, $this->resolvedDocPossibleAliases, \true)) {
            return \true;
        }
        return (bool) $this->fullyQualifiedFromUseFinder->matchAliasNamespace($use, $loweredAliasName);
    }
    private function refactorAliasName(\PhpParser\Node\Stmt\Use_ $use, string $fullUseUseName, string $lastName, \PhpParser\Node\Stmt\UseUse $useUse) : void
    {
        $parentUse = $use->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentUse instanceof \PhpParser\Node) {
            return;
        }
        /** @var Use_[] $uses */
        $uses = $this->betterNodeFinder->find($parentUse, function (\PhpParser\Node $node) use($use) : bool {
            if ($node === $use) {
                return \false;
            }
            return $node instanceof \PhpParser\Node\Stmt\Use_;
        });
        if ($this->classNameImportSkipper->isShortNameInUseStatement(new \PhpParser\Node\Name($lastName), $uses)) {
            return;
        }
        $loweredFullUseUseName = \strtolower($fullUseUseName);
        if (!isset($this->resolvedNamesAndParentsByShortName[$loweredFullUseUseName])) {
            return;
        }
        $this->nameRenamer->renameNameNode($this->resolvedNamesAndParentsByShortName[$loweredFullUseUseName], $lastName);
        $useUse->alias = null;
    }
    private function hasUseAlias(\PhpParser\Node\Stmt\Use_ $use) : bool
    {
        foreach ($use->uses as $useUse) {
            if ($useUse->alias !== null) {
                return \true;
            }
        }
        return \false;
    }
    private function resolveSearchNode(\PhpParser\Node\Stmt\Use_ $use) : ?\PhpParser\Node
    {
        $searchNode = $use->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($searchNode !== null) {
            return $searchNode;
        }
        return $use->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
    }
}

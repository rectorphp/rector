<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Naming\Naming\AliasNameResolver;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class NameImportingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\Node\NameImporter
     */
    private $nameImporter;
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper
     */
    private $classNameImportSkipper;
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
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NameImporter $nameImporter, ClassNameImportSkipper $classNameImportSkipper, UseImportsResolver $useImportsResolver, AliasNameResolver $aliasNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nameImporter = $nameImporter;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->useImportsResolver = $useImportsResolver;
        $this->aliasNameResolver = $aliasNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return \PhpParser\Node|int|null
     */
    public function enterNode(Node $node)
    {
        if (!$node instanceof FullyQualified) {
            return null;
        }
        if ($node->isSpecialClassName()) {
            return null;
        }
        $currentUses = $this->useImportsResolver->resolve();
        if ($this->classNameImportSkipper->shouldSkipName($node, $currentUses)) {
            return null;
        }
        // make use of existing use import
        $nameInUse = $this->resolveNameInUse($node, $currentUses);
        if ($nameInUse instanceof Name) {
            return $nameInUse;
        }
        return $this->nameImporter->importName($node, $this->getFile());
    }
    /**
     * @param Stmt[] $stmts
     */
    public function shouldTraverse(array $stmts) : bool
    {
        $namespaces = $this->betterNodeFinder->findInstanceOf($stmts, Namespace_::class);
        // skip if 2 namespaces are present
        if (\count($namespaces) > 1) {
            return \false;
        }
        return !$this->betterNodeFinder->hasInstancesOf($stmts, [InlineHTML::class]);
    }
    /**
     * @param array<Use_|GroupUse> $currentUses
     */
    private function resolveNameInUse(FullyQualified $fullyQualified, array $currentUses) : ?\PhpParser\Node\Name
    {
        $aliasName = $this->aliasNameResolver->resolveByName($fullyQualified, $currentUses);
        if (\is_string($aliasName)) {
            return new Name($aliasName);
        }
        if (\substr_count($fullyQualified->toCodeString(), '\\') === 1) {
            return null;
        }
        $lastName = $fullyQualified->getLast();
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
}

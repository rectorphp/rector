<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Naming\Naming\AliasNameResolver;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Guard\AddUseStatementGuard;
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
     * @var \Rector\PostRector\Guard\AddUseStatementGuard
     */
    private $addUseStatementGuard;
    /**
     * @var array<Use_|GroupUse>
     */
    private $currentUses = [];
    public function __construct(NameImporter $nameImporter, ClassNameImportSkipper $classNameImportSkipper, UseImportsResolver $useImportsResolver, AliasNameResolver $aliasNameResolver, AddUseStatementGuard $addUseStatementGuard)
    {
        $this->nameImporter = $nameImporter;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->useImportsResolver = $useImportsResolver;
        $this->aliasNameResolver = $aliasNameResolver;
        $this->addUseStatementGuard = $addUseStatementGuard;
    }
    public function beforeTraverse(array $nodes)
    {
        $this->currentUses = $this->useImportsResolver->resolve();
        return $nodes;
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
        if ($this->classNameImportSkipper->shouldSkipName($node, $this->currentUses)) {
            return null;
        }
        // make use of existing use import
        $nameInUse = $this->resolveNameInUse($node);
        if ($nameInUse instanceof Name) {
            $nameInUse->setAttribute(AttributeKey::NAMESPACED_NAME, $node->toString());
            return $nameInUse;
        }
        return $this->nameImporter->importName($node, $this->getFile());
    }
    /**
     * @param Stmt[] $stmts
     */
    public function shouldTraverse(array $stmts) : bool
    {
        return $this->addUseStatementGuard->shouldTraverse($stmts, $this->getFile()->getFilePath());
    }
    private function resolveNameInUse(FullyQualified $fullyQualified) : ?\PhpParser\Node\Name
    {
        $aliasName = $this->aliasNameResolver->resolveByName($fullyQualified, $this->currentUses);
        if (\is_string($aliasName)) {
            return new Name($aliasName);
        }
        if (\substr_count($fullyQualified->toCodeString(), '\\') === 1) {
            return null;
        }
        $lastName = $fullyQualified->getLast();
        foreach ($this->currentUses as $currentUse) {
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

<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use Rector\CodingStyle\ClassNameImport\ValueObject\UsedImports;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class UsedImportsResolver
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private \Rector\CodingStyle\ClassNameImport\UseImportsTraverser $useImportsTraverser;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, \Rector\CodingStyle\ClassNameImport\UseImportsTraverser $useImportsTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useImportsTraverser = $useImportsTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param Stmt[] $stmts
     */
    public function resolveForStmts(array $stmts) : UsedImports
    {
        $usedImports = [];
        /** @var Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf($stmts, Class_::class);
        // add class itself
        // is not anonymous class
        if ($class instanceof Class_) {
            $className = (string) $this->nodeNameResolver->getName($class);
            $usedImports[] = new FullyQualifiedObjectType($className);
        }
        $usedConstImports = [];
        $usedFunctionImports = [];
        /** @param Use_::TYPE_* $useType */
        $this->useImportsTraverser->traverserStmts($stmts, static function (int $useType, UseItem $useItem, string $name) use(&$usedImports, &$usedFunctionImports, &$usedConstImports) : void {
            if ($useType === Use_::TYPE_NORMAL) {
                if ($useItem->alias instanceof Identifier) {
                    $usedImports[] = new AliasedObjectType($useItem->alias->toString(), $name);
                } else {
                    $usedImports[] = new FullyQualifiedObjectType($name);
                }
            }
            if ($useType === Use_::TYPE_FUNCTION) {
                $usedFunctionImports[] = new FullyQualifiedObjectType($name);
            }
            if ($useType === Use_::TYPE_CONSTANT) {
                $usedConstImports[] = new FullyQualifiedObjectType($name);
            }
        });
        return new UsedImports($usedImports, $usedFunctionImports, $usedConstImports);
    }
}

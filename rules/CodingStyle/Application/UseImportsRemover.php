<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Application;

use RectorPrefix202310\Nette\Utils\Strings;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Use_;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class UseImportsRemover
{
    /**
     * @param Stmt[] $stmts
     * @param string[] $removedUses
     * @param AliasedObjectType[]|FullyQualifiedObjectType[] $useImportTypes
     * @return Stmt[]
     */
    public function removeImportsFromStmts(array $stmts, array $removedUses, array $useImportTypes) : array
    {
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof Use_) {
                continue;
            }
            $stmt = $this->removeUseFromUse($removedUses, $stmt, $useImportTypes);
            // remove empty uses
            if ($stmt->uses === []) {
                unset($stmts[$key]);
            }
        }
        return $stmts;
    }
    /**
     * @param string[] $removedUses
     * @param AliasedObjectType[]|FullyQualifiedObjectType[] $useImportTypes
     */
    private function removeUseFromUse(array $removedUses, Use_ $use, array $useImportTypes) : Use_
    {
        foreach ($use->uses as $usesKey => $useUse) {
            $useName = $useUse->name->toString();
            if (!\in_array($useName, $removedUses, \true)) {
                continue;
            }
            $lastUseName = Strings::after($useName, '\\', -1);
            foreach ($useImportTypes as $useImportType) {
                $className = $useImportType instanceof AliasedObjectType ? $useImportType->getFullyQualifiedName() : $useImportType->getClassName();
                if ($className === $useName || Strings::after($className, '\\', -1) === $lastUseName) {
                    unset($use->uses[$usesKey]);
                    continue 2;
                }
            }
        }
        return $use;
    }
}

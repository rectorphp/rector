<?php declare (strict_types=1);

namespace Rector\BetterReflection\TypesFinder\PhpDocumentor;

use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

class NamespaceNodeToReflectionTypeContext
{
    /**
     * @return string[] indexed by alias
     */
    private function aliasesToFullyQualifiedNames(Namespace_ $namespace) : array
    {
        // flatten(flatten(map(stuff)))
        return \array_merge([], ...\array_merge([], ...\array_map(function ($use) : array {
            /** @var $use Use_|GroupUse */

            return \array_map(function (UseUse $useUse) use ($use) : array {
                if ($use instanceof GroupUse) {
                    return [$useUse->alias => $use->prefix->toString() . '\\' . $useUse->name->toString()];
                }

                return [$useUse->alias => $useUse->name->toString()];
            }, $use->uses);
        }, $this->classAlikeUses($namespace))));
    }
}

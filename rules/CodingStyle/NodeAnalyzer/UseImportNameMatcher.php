<?php

declare(strict_types=1);

namespace Rector\CodingStyle\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

final class UseImportNameMatcher
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    /**
     * @param Stmt[] $stmts
     */
    public function matchNameWithStmts(string $tag, array $stmts): ?string
    {
        /** @var Use_[] $uses */
        $uses = $this->betterNodeFinder->findInstanceOf($stmts, Use_::class);
        return $this->matchNameWithUses($tag, $uses);
    }

    /**
     * @param Use_[] $uses
     */
    public function matchNameWithUses(string $tag, array $uses): ?string
    {
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if (! $this->isUseMatchingName($tag, $useUse)) {
                    continue;
                }

                return $this->resolveName($tag, $useUse);
            }
        }

        return null;
    }

    private function isUseMatchingName(string $tag, UseUse $useUse): bool
    {
        $shortName = $useUse->alias !== null ? $useUse->alias->name : $useUse->name->getLast();
        $shortNamePattern = preg_quote($shortName, '#');

        return (bool) Strings::match($tag, '#' . $shortNamePattern . '(\\\\[\w]+)?$#i');
    }

    private function resolveName(string $tag, UseUse $useUse): string
    {
        if ($useUse->alias === null) {
            return $useUse->name->toString();
        }

        $unaliasedShortClass = Strings::substring($tag, Strings::length($useUse->alias->toString()));
        if (\str_starts_with($unaliasedShortClass, '\\')) {
            return $useUse->name . $unaliasedShortClass;
        }

        return $useUse->name . '\\' . $unaliasedShortClass;
    }
}

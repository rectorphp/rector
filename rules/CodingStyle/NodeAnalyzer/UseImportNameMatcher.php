<?php

declare(strict_types=1);

namespace Rector\CodingStyle\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Util\StringUtils;

final class UseImportNameMatcher
{
    /**
     * @var string
     *
     * @see https://regex101.com/r/ZxFSlc/1 for last name, eg: Entity and UniqueEntity
     * @see https://regex101.com/r/OLO0Un/1 for inside namespace, eg: ORM for ORM\Id or ORM\Column
     */
    private const SHORT_NAME_REGEX = '#^%s(\\\\[\w]+)?$#i';

    public function __construct(
        private readonly BetterNodeFinder $betterNodeFinder
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
        $pattern = sprintf(self::SHORT_NAME_REGEX, $shortNamePattern);
        return StringUtils::isMatch($tag, $pattern);
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

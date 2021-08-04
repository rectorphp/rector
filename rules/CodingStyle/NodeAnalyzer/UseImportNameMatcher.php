<?php

declare (strict_types=1);
namespace Rector\CodingStyle\NodeAnalyzer;

use RectorPrefix20210804\Nette\Utils\Strings;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class UseImportNameMatcher
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param Stmt[] $stmts
     */
    public function matchNameWithStmts(string $tag, array $stmts) : ?string
    {
        /** @var Use_[] $uses */
        $uses = $this->betterNodeFinder->findInstanceOf($stmts, \PhpParser\Node\Stmt\Use_::class);
        return $this->matchNameWithUses($tag, $uses);
    }
    /**
     * @param Use_[] $uses
     */
    public function matchNameWithUses(string $tag, array $uses) : ?string
    {
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if (!$this->isUseMatchingName($tag, $useUse)) {
                    continue;
                }
                return $this->resolveName($tag, $useUse);
            }
        }
        return null;
    }
    private function isUseMatchingName(string $tag, \PhpParser\Node\Stmt\UseUse $useUse) : bool
    {
        $shortName = $useUse->alias !== null ? $useUse->alias->name : $useUse->name->getLast();
        $shortNamePattern = \preg_quote($shortName, '#');
        return (bool) \RectorPrefix20210804\Nette\Utils\Strings::match($tag, '#' . $shortNamePattern . '(\\\\[\\w]+)?$#i');
    }
    private function resolveName(string $tag, \PhpParser\Node\Stmt\UseUse $useUse) : string
    {
        if ($useUse->alias === null) {
            return $useUse->name->toString();
        }
        $unaliasedShortClass = \RectorPrefix20210804\Nette\Utils\Strings::substring($tag, \RectorPrefix20210804\Nette\Utils\Strings::length($useUse->alias->toString()));
        if (\strncmp($unaliasedShortClass, '\\', \strlen('\\')) === 0) {
            return $useUse->name . $unaliasedShortClass;
        }
        return $useUse->name . '\\' . $unaliasedShortClass;
    }
}

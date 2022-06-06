<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\NodeAnalyzer;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Node\Stmt\GroupUse;
use RectorPrefix20220606\PhpParser\Node\Stmt\Use_;
use RectorPrefix20220606\PhpParser\Node\Stmt\UseUse;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\Core\Util\StringUtils;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
final class UseImportNameMatcher
{
    /**
     * @var string
     *
     * @see https://regex101.com/r/ZxFSlc/1 for last name, eg: Entity and UniqueEntity
     * @see https://regex101.com/r/OLO0Un/1 for inside namespace, eg: ORM for ORM\Id or ORM\Column
     */
    private const SHORT_NAME_REGEX = '#^%s(\\\\[\\w]+)?$#i';
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param Stmt[] $stmts
     */
    public function matchNameWithStmts(string $tag, array $stmts) : ?string
    {
        /** @var Use_[] $uses */
        $uses = $this->betterNodeFinder->findInstanceOf($stmts, Use_::class);
        return $this->matchNameWithUses($tag, $uses);
    }
    /**
     * @param Use_[]|GroupUse[] $uses
     */
    public function matchNameWithUses(string $tag, array $uses) : ?string
    {
        foreach ($uses as $use) {
            $prefix = $use instanceof GroupUse ? $use->prefix . '\\' : '';
            foreach ($use->uses as $useUse) {
                if (!$this->isUseMatchingName($tag, $useUse)) {
                    continue;
                }
                return $this->resolveName($prefix, $tag, $useUse);
            }
        }
        return null;
    }
    public function resolveName(string $prefix, string $tag, UseUse $useUse) : string
    {
        // useuse can be renamed on the fly, so just in case, use the original one
        $originalUseUse = $useUse->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$originalUseUse instanceof UseUse) {
            throw new ShouldNotHappenException();
        }
        if ($originalUseUse->alias === null) {
            return $prefix . $originalUseUse->name->toString();
        }
        $unaliasedShortClass = Strings::substring($tag, Strings::length($originalUseUse->alias->toString()));
        if (\strncmp($unaliasedShortClass, '\\', \strlen('\\')) === 0) {
            return $prefix . $originalUseUse->name . $unaliasedShortClass;
        }
        return $prefix . $originalUseUse->name . '\\' . $unaliasedShortClass;
    }
    private function isUseMatchingName(string $tag, UseUse $useUse) : bool
    {
        // useuse can be renamed on the fly, so just in case, use the original one
        $originalUseUse = $useUse->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$originalUseUse instanceof UseUse) {
            return \false;
        }
        $shortName = $originalUseUse->alias instanceof Identifier ? $originalUseUse->alias->name : $originalUseUse->name->getLast();
        $shortNamePattern = \preg_quote($shortName, '#');
        $pattern = \sprintf(self::SHORT_NAME_REGEX, $shortNamePattern);
        return StringUtils::isMatch($tag, $pattern);
    }
}

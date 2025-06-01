<?php

declare (strict_types=1);
namespace Rector\CodingStyle\NodeAnalyzer;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use Rector\Exception\ShouldNotHappenException;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Util\StringUtils;
final class UseImportNameMatcher
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private UseImportsResolver $useImportsResolver;
    /**
     * @var string
     *
     * @see https://regex101.com/r/ZxFSlc/1 for last name, eg: Entity and UniqueEntity
     * @see https://regex101.com/r/OLO0Un/1 for inside namespace, eg: ORM for ORM\Id or ORM\Column
     */
    private const SHORT_NAME_REGEX = '#^%s(\\\\[\\w]+)?$#i';
    public function __construct(BetterNodeFinder $betterNodeFinder, UseImportsResolver $useImportsResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useImportsResolver = $useImportsResolver;
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
     * @param array<Use_|GroupUse> $uses
     * @return non-empty-string|null
     */
    public function matchNameWithUses(string $tag, array $uses) : ?string
    {
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            foreach ($use->uses as $useUse) {
                if (!$this->isUseMatchingName($tag, $useUse)) {
                    continue;
                }
                return $this->resolveName($prefix, $tag, $useUse);
            }
        }
        return null;
    }
    /**
     * @return non-empty-string
     */
    private function resolveName(string $prefix, string $tag, UseItem $useItem) : string
    {
        // useuse can be renamed on the fly, so just in case, use the original one
        $originalUseUseNode = $useItem->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$originalUseUseNode instanceof UseItem) {
            throw new ShouldNotHappenException();
        }
        if (!$originalUseUseNode->alias instanceof Identifier) {
            $lastName = $originalUseUseNode->name->getLast();
            if (\strncmp($tag, $lastName . '\\', \strlen($lastName . '\\')) === 0) {
                $tagName = Strings::after($tag, '\\');
                return $prefix . $originalUseUseNode->name->toString() . '\\' . $tagName;
            }
            return $prefix . $originalUseUseNode->name->toString();
        }
        $unaliasedShortClass = Strings::substring($tag, \strlen($originalUseUseNode->alias->toString()));
        if (\strncmp($unaliasedShortClass, '\\', \strlen('\\')) === 0) {
            return $prefix . $originalUseUseNode->name . $unaliasedShortClass;
        }
        return $prefix . $originalUseUseNode->name . '\\' . $unaliasedShortClass;
    }
    private function isUseMatchingName(string $tag, UseItem $useItem) : bool
    {
        // useuse can be renamed on the fly, so just in case, use the original one
        $originalUseUseNode = $useItem->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$originalUseUseNode instanceof UseItem) {
            return \false;
        }
        $shortName = $originalUseUseNode->alias instanceof Identifier ? $originalUseUseNode->alias->name : $originalUseUseNode->name->getLast();
        $shortNamePattern = \preg_quote($shortName, '#');
        $pattern = \sprintf(self::SHORT_NAME_REGEX, $shortNamePattern);
        return StringUtils::isMatch($tag, $pattern);
    }
}

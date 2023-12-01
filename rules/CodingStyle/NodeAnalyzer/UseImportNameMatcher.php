<?php

declare (strict_types=1);
namespace Rector\CodingStyle\NodeAnalyzer;

use RectorPrefix202312\Nette\Utils\Strings;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Util\StringUtils;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class UseImportNameMatcher
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
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
     * @param Use_[]|GroupUse[] $uses
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
    private function resolveName(string $prefix, string $tag, UseUse $useUse) : string
    {
        // useuse can be renamed on the fly, so just in case, use the original one
        $originalUseUseNode = $useUse->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$originalUseUseNode instanceof UseUse) {
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
    private function isUseMatchingName(string $tag, UseUse $useUse) : bool
    {
        // useuse can be renamed on the fly, so just in case, use the original one
        $originalUseUseNode = $useUse->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$originalUseUseNode instanceof UseUse) {
            return \false;
        }
        $shortName = $originalUseUseNode->alias instanceof Identifier ? $originalUseUseNode->alias->name : $originalUseUseNode->name->getLast();
        $shortNamePattern = \preg_quote($shortName, '#');
        $pattern = \sprintf(self::SHORT_NAME_REGEX, $shortNamePattern);
        return StringUtils::isMatch($tag, $pattern);
    }
}

<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Node\Attribute;

/**
 * Read-only utils for ClassLike|Class_|Trait_|Interface_ Node:
 * "class" SomeClass, "interface" Interface, "trait" Trait
 *
 * @todo decouple to Class_|Trait_|Interface_ TypeResolvers and remove this class
 * This is used nowhere else
 */
final class ClassLikeAnalyzer
{
    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    public function __construct(SmartClassReflector $smartClassReflector)
    {
        $this->smartClassReflector = $smartClassReflector;
    }

    public function isAnonymousClassNode(Node $node): bool
    {
        return $node instanceof Class_ && $node->isAnonymous();
    }

    public function isNormalClass(Node $node): bool
    {
        return $node instanceof Class_ && ! $node->isAnonymous();
    }

    /**
     * @return string[]
     */
    public function resolveTypeAndParentTypes(ClassLike $classLikeNode): array
    {
        $types = [];

        if (! $this->isAnonymousClassNode($classLikeNode)) {
            $className = $this->resolveNameNode($classLikeNode);
            $types[] = $className;

            if ($classLikeNode instanceof Class_ || $classLikeNode instanceof Interface_) {
                $types = array_merge($types, $this->resolveExtendsTypes($classLikeNode, $className));
            }
        } else {
            $types = array_merge($types, $this->resolveExtendsTypes($classLikeNode));
        }

        if ($classLikeNode instanceof Class_) {
            $types = array_merge($types, $this->resolveImplementsTypes($classLikeNode));
            $types = array_merge($types, $this->resolveUsedTraitTypes($classLikeNode));
        }

        if ($classLikeNode instanceof Trait_) {
            $types = array_merge($types, $this->resolveUsedTraitTypes($classLikeNode));
        }

        return $types;
    }

    /**
     * @param Name|ClassLike $node
     */
    private function resolveNameNode(Node $node): string
    {
        $name = (string) $node->getAttribute(Attribute::CLASS_NAME);
        if ($name) {
            return $name;
        }

        $namespacedName = $node->getAttribute('namespacedName');
        if ($namespacedName instanceof FullyQualified) {
            return $namespacedName->toString();
        }

        $nameNode = $node->getAttribute(Attribute::RESOLVED_NAME);
        if ($nameNode instanceof Name) {
            return $nameNode->toString();
        }

        if ($node instanceof Name) {
            return $node->toString();
        }

        return (string) $node->name;
    }

    /**
     * @param Class_|Interface_ $classLikeNode
     * @return string[]
     */
    private function resolveExtendsTypes(ClassLike $classLikeNode, ?string $className = null): array
    {
        if (! $classLikeNode->extends) {
            return [];
        }

        return $this->smartClassReflector->getClassParents($className, $classLikeNode);
    }

    /**
     * @return string[]
     */
    private function resolveImplementsTypes(Class_ $classNode): array
    {
        return array_map(function (Name $interface): string {
            /** @var FullyQualified $interface */
            return $interface->toString();
        }, $classNode->implements);
    }

    /**
     * @param Class_|Trait_ $classOrTraitNode
     * @return string[]
     */
    private function resolveUsedTraitTypes(ClassLike $classOrTraitNode): array
    {
        $usedTraits = [];

        foreach ($classOrTraitNode->stmts as $stmt) {
            if (! $stmt instanceof TraitUse) {
                continue;
            }

            foreach ($stmt->traits as $trait) {
                if ($trait->hasAttribute(Attribute::RESOLVED_NAME)) {
                    $usedTraits[] = (string) $trait->getAttribute(Attribute::RESOLVED_NAME);
                }
            }
        }

        return $usedTraits;
    }
}

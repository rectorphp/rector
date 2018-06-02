<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

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

abstract class AbstractClassLikeTypeResolver
{
    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    /**
     * @required
     */
    public function setSmartClassReflector(SmartClassReflector $smartClassReflector): void
    {
        $this->smartClassReflector = $smartClassReflector;
    }

    /**
     * @param Name|ClassLike $node
     */
    protected function resolveNameNode(Node $node): string
    {
        $name = (string) $node->getAttribute(Attribute::CLASS_NAME);
        if ($name) {
            return $name;
        }

        $namespacedName = $node->getAttribute(Attribute::NAMESPACED_NAME);
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
    protected function resolveExtendsTypes(ClassLike $classLikeNode, ?string $className = null): array
    {
        if (! $classLikeNode->extends) {
            return [];
        }

        if ($classLikeNode instanceof Interface_ && $className) {
            return $this->smartClassReflector->getInterfaceParents($className);
        }

        return $this->smartClassReflector->getClassParents($className, $classLikeNode);
    }

    /**
     * @param Class_|Trait_ $classOrTraitNode
     * @return string[]
     */
    protected function resolveUsedTraitTypes(ClassLike $classOrTraitNode): array
    {
        foreach ($classOrTraitNode->stmts as $stmt) {
            if (! $stmt instanceof TraitUse) {
                continue;
            }

            return $this->resolveTraitNamesFromTraitUse($stmt);
        }

        return [];
    }

    /**
     * @return string[]
     */
    protected function resolveImplementsTypes(Class_ $classNode): array
    {
        return array_map(function (Name $interface): string {
            if ($interface->hasAttribute(Attribute::RESOLVED_NAME)) {
                return (string) $interface->getAttribute(Attribute::RESOLVED_NAME);
            }

            return $interface->toString();
        }, $classNode->implements);
    }

    /**
     * @return string[]
     */
    private function resolveTraitNamesFromTraitUse(TraitUse $traitUse): array
    {
        $usedTraits = [];

        foreach ($traitUse->traits as $trait) {
            if ($trait->hasAttribute(Attribute::RESOLVED_NAME)) {
                $usedTraits[] = (string) $trait->getAttribute(Attribute::RESOLVED_NAME);
            }
        }

        return $usedTraits;
    }
}

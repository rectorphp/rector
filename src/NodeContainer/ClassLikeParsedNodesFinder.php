<?php

declare(strict_types=1);

namespace Rector\NodeContainer;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class ClassLikeParsedNodesFinder
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(ParsedNodesByType $parsedNodesByType, NameResolver $nameResolver)
    {
        $this->parsedNodesByType = $parsedNodesByType;
        $this->nameResolver = $nameResolver;
    }

    /**
     * @return Class_[]
     */
    public function findChildrenOfClass(string $class): array
    {
        $childrenClasses = [];

        foreach ($this->parsedNodesByType->getClasses() as $classNode) {
            $currentClassName = $classNode->getAttribute(AttributeKey::CLASS_NAME);
            if (! $this->isChildOrEqualClassLike($class, $currentClassName)) {
                continue;
            }

            $childrenClasses[] = $classNode;
        }

        return $childrenClasses;
    }

    /**
     * @return Class_[]
     */
    public function findClassesBySuffix(string $suffix): array
    {
        $classNodes = [];

        foreach ($this->parsedNodesByType->getClasses() as $className => $classNode) {
            if (! Strings::endsWith($className, $suffix)) {
                continue;
            }

            $classNodes[] = $classNode;
        }

        return $classNodes;
    }

    public function hasClassChildren(string $class): bool
    {
        return $this->findChildrenOfClass($class) !== [];
    }

    /**
     * @return Trait_[]
     */
    public function findUsedTraitsInClass(ClassLike $classLike): array
    {
        $traits = [];

        foreach ($classLike->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $traitName = $this->nameResolver->getName($trait);
                if ($traitName === null) {
                    continue;
                }

                $foundTrait = $this->parsedNodesByType->findTrait($traitName);
                if ($foundTrait !== null) {
                    $traits[] = $foundTrait;
                }
            }
        }

        return $traits;
    }

    /**
     * @return Class_[]|Interface_[]
     */
    public function findClassesAndInterfacesByType(string $type): array
    {
        return array_merge($this->findChildrenOfClass($type), $this->findImplementersOfInterface($type));
    }

    /**
     * @return Interface_[]
     */
    public function findImplementersOfInterface(string $interface): array
    {
        $implementerInterfaces = [];

        foreach ($this->parsedNodesByType->getInterfaces() as $interfaceNode) {
            $className = $interfaceNode->getAttribute(AttributeKey::CLASS_NAME);

            if (! $this->isChildOrEqualClassLike($interface, $className)) {
                continue;
            }

            $implementerInterfaces[] = $interfaceNode;
        }

        return $implementerInterfaces;
    }

    private function isChildOrEqualClassLike(string $desiredClass, ?string $currentClassName): bool
    {
        if ($currentClassName === null) {
            return false;
        }

        if (! is_a($currentClassName, $desiredClass, true)) {
            return false;
        }

        if ($currentClassName === $desiredClass) {
            return false;
        }

        return true;
    }
}

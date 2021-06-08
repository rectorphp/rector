<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\TraitUse;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;

final class ExternalFullyQualifiedAnalyzer
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private NodeRepository $nodeRepository
    ) {
    }

    public function hasExternalFullyQualifieds(ClassLike $classLike): bool
    {
        if ($classLike instanceof Class_ || $classLike instanceof Interface_) {
            $extends = $classLike->extends ?? [];
        } else {
            $extends = [];
        }

        /** @var FullyQualified[] $extends */
        $extends = $extends instanceof FullyQualified
            ? [$extends]
            : $extends;

        /** @var FullyQualified[] $implements */
        $implements = $classLike instanceof Class_ ? $classLike->implements : [];

        $parentClassesAndInterfaces = array_merge($extends, $implements);
        $hasExternalClassOrInterface = $this->hasExternalClassOrInterface($parentClassesAndInterfaces);
        if ($hasExternalClassOrInterface) {
            return true;
        }

        /** @var TraitUse[] $traitUses */
        $traitUses = $classLike->getTraitUses();
        return $this->hasExternalTrait($traitUses);
    }

    /**
     * @param FullyQualified[] $fullyQualifiedClassLikes
     */
    private function hasExternalClassOrInterface(array $fullyQualifiedClassLikes): bool
    {
        if ($fullyQualifiedClassLikes === []) {
            return false;
        }

        foreach ($fullyQualifiedClassLikes as $fullyQualifiedClassLike) {
            /** @var string $className */
            $className = $this->nodeNameResolver->getName($fullyQualifiedClassLike);
            $isClassFound = (bool) $this->nodeRepository->findClass($className);
            $isInterfaceFound = (bool) $this->nodeRepository->findInterface($className);
            if ($isClassFound) {
                continue;
            }
            if ($isInterfaceFound) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param TraitUse[] $traitUses
     */
    private function hasExternalTrait(array $traitUses): bool
    {
        if ($traitUses === []) {
            return false;
        }

        foreach ($traitUses as $traitUse) {
            $traits = $traitUse->traits;

            foreach ($traits as $trait) {
                if (! $trait instanceof FullyQualified) {
                    return false;
                }

                /** @var string $traitName */
                $traitName = $this->nodeNameResolver->getName($trait);
                $isTraitFound = (bool) $this->nodeRepository->findTrait($traitName);

                if (! $isTraitFound) {
                    return true;
                }
            }
        }

        return false;
    }
}

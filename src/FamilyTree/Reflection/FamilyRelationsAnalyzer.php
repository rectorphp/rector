<?php

declare (strict_types=1);
namespace Rector\FamilyTree\Reflection;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Broker\ClassNotFoundException;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\ShouldNotHappenException;
use Rector\Caching\Cache;
use Rector\Caching\Enum\CacheKey;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\Util\Reflection\PrivatesAccessor;
final class FamilyRelationsAnalyzer
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Util\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider
     */
    private $dynamicSourceLocatorProvider;
    /**
     * @readonly
     * @var \Rector\Caching\Cache
     */
    private $cache;
    /**
     * @var bool
     */
    private $hasClassNamesCachedOrLoadOneLocator = \false;
    public function __construct(ReflectionProvider $reflectionProvider, NodeNameResolver $nodeNameResolver, PrivatesAccessor $privatesAccessor, DynamicSourceLocatorProvider $dynamicSourceLocatorProvider, Cache $cache, bool $hasClassNamesCachedOrLoadOneLocator = \false)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->privatesAccessor = $privatesAccessor;
        $this->dynamicSourceLocatorProvider = $dynamicSourceLocatorProvider;
        $this->cache = $cache;
        $this->hasClassNamesCachedOrLoadOneLocator = $hasClassNamesCachedOrLoadOneLocator;
    }
    /**
     * @return ClassReflection[]
     */
    public function getChildrenOfClassReflection(ClassReflection $desiredClassReflection) : array
    {
        if ($desiredClassReflection->isFinalByKeyword()) {
            return [];
        }
        $this->loadClasses();
        /** @var ClassReflection[] $classReflections */
        $classReflections = $this->privatesAccessor->getPrivateProperty($this->reflectionProvider, 'classes');
        $childrenClassReflections = [];
        foreach ($classReflections as $classReflection) {
            if (!$classReflection->isSubclassOf($desiredClassReflection->getName())) {
                continue;
            }
            $childrenClassReflections[] = $classReflection;
        }
        return $childrenClassReflections;
    }
    /**
     * @api
     * @return string[]
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Name $classOrName
     */
    public function getClassLikeAncestorNames($classOrName) : array
    {
        $ancestorNames = [];
        if ($classOrName instanceof Name) {
            $fullName = $this->nodeNameResolver->getName($classOrName);
            if (!$this->reflectionProvider->hasClass($fullName)) {
                return [];
            }
            $classReflection = $this->reflectionProvider->getClass($fullName);
            $ancestors = \array_merge($classReflection->getParents(), $classReflection->getInterfaces());
            return \array_map(static function (ClassReflection $classReflection) : string {
                return $classReflection->getName();
            }, $ancestors);
        }
        if ($classOrName instanceof Interface_) {
            foreach ($classOrName->extends as $extendInterfaceName) {
                $ancestorNames[] = $this->nodeNameResolver->getName($extendInterfaceName);
                $ancestorNames = \array_merge($ancestorNames, $this->getClassLikeAncestorNames($extendInterfaceName));
            }
        }
        if ($classOrName instanceof Class_) {
            if ($classOrName->extends instanceof Name) {
                $ancestorNames[] = $this->nodeNameResolver->getName($classOrName->extends);
                $ancestorNames = \array_merge($ancestorNames, $this->getClassLikeAncestorNames($classOrName->extends));
            }
            foreach ($classOrName->implements as $implement) {
                $ancestorNames[] = $this->nodeNameResolver->getName($implement);
                $ancestorNames = \array_merge($ancestorNames, $this->getClassLikeAncestorNames($implement));
            }
        }
        /** @var string[] $ancestorNames */
        return $ancestorNames;
    }
    private function loadClasses() : void
    {
        if ($this->hasClassNamesCachedOrLoadOneLocator) {
            return;
        }
        $key = $this->dynamicSourceLocatorProvider->getCacheClassNameKey();
        $classNamesCache = $this->cache->load($key, CacheKey::CLASSNAMES_HASH_KEY);
        if (\is_array($classNamesCache)) {
            foreach ($classNamesCache as $classNameCache) {
                try {
                    $this->reflectionProvider->getClass($classNameCache);
                } catch (ClassNotFoundException|ShouldNotHappenException $exception) {
                }
            }
        }
        $this->hasClassNamesCachedOrLoadOneLocator = \true;
    }
}

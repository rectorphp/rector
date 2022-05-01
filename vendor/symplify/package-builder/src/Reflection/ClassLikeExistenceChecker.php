<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\PackageBuilder\Reflection;

use ReflectionClass;
/**
 * @api
 */
final class ClassLikeExistenceChecker
{
    /**
     * @var string[]
     */
    private $sensitiveExistingClasses = [];
    /**
     * @var string[]
     */
    private $sensitiveNonExistingClasses = [];
    public function doesClassLikeExist(string $classLike) : bool
    {
        if (\class_exists($classLike)) {
            return \true;
        }
        if (\interface_exists($classLike)) {
            return \true;
        }
        return \trait_exists($classLike);
    }
    public function doesClassLikeInsensitiveExists(string $classLikeName) : bool
    {
        if (!$this->doesClassLikeExist($classLikeName)) {
            return \false;
        }
        // already known values
        if (\in_array($classLikeName, $this->sensitiveExistingClasses, \true)) {
            return \true;
        }
        if (\in_array($classLikeName, $this->sensitiveNonExistingClasses, \true)) {
            return \false;
        }
        $reflectionClass = new \ReflectionClass($classLikeName);
        if ($classLikeName !== $reflectionClass->getName()) {
            $this->sensitiveNonExistingClasses[] = $classLikeName;
            return \false;
        }
        $this->sensitiveExistingClasses[] = $classLikeName;
        return \true;
    }
}

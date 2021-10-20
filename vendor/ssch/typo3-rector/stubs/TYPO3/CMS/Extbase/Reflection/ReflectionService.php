<?php

namespace RectorPrefix20211020\TYPO3\CMS\Extbase\Reflection;

if (\class_exists('TYPO3\\CMS\\Extbase\\Reflection\\ReflectionService')) {
    return;
}
class ReflectionService
{
    /**
     * @return mixed[]
     */
    public function getClassTagsValues($className)
    {
        $classSchema = $this->getClassSchema($className);
        return $classSchema->getTags();
    }
    /**
     * @return mixed[]
     */
    public function getClassTagValues($className, $tag)
    {
        $classSchema = $this->getClassSchema($className);
        return isset($classSchema->getTags()[$tag]) ? $classSchema->getTags()[$tag] : [];
    }
    /**
     * @return mixed[]
     */
    public function getClassPropertyNames($className)
    {
        $classSchema = $this->getClassSchema($className);
        return \array_keys($classSchema->getProperties());
    }
    /**
     * @return \TYPO3\CMS\Extbase\Reflection\ClassSchema
     */
    public function getClassSchema($classNameOrObject)
    {
        return new \RectorPrefix20211020\TYPO3\CMS\Extbase\Reflection\ClassSchema();
    }
    /**
     * @return bool
     */
    public function hasMethod($className, $methodName)
    {
        $classSchema = $this->getClassSchema($className);
        return $classSchema->hasMethod($methodName);
    }
    /**
     * @return mixed[]
     */
    public function getMethodTagsValues($className, $methodName)
    {
        return isset($this->getClassSchema($className)->getMethod($methodName)['tags']) ? $this->getClassSchema($className)->getMethod($methodName)['tags'] : [];
    }
    /**
     * @return mixed[]
     */
    public function getMethodParameters($className, $methodName)
    {
        $classSchema = $this->getClassSchema($className);
        return isset($classSchema->getMethod($methodName)['params']) ? $classSchema->getMethod($methodName)['params'] : [];
    }
    /**
     * @return mixed[]
     */
    public function getPropertyTagsValues($className, $propertyName)
    {
        $classSchema = $this->getClassSchema($className);
        return isset($classSchema->getProperty($propertyName)['tags']) ? $classSchema->getProperty($propertyName)['tags'] : [];
    }
    /**
     * @return mixed[]
     */
    public function getPropertyTagValues($className, $propertyName, $tag)
    {
        $classSchema = $this->getClassSchema($className);
        return isset($classSchema->getProperty($propertyName)['tags'][$tag]) ? $classSchema->getProperty($propertyName)['tags'][$tag] : [];
    }
    /**
     * @return bool
     */
    public function isClassTaggedWith($className, $tag)
    {
        $classSchema = $this->getClassSchema($className);
        return (bool) \count(\array_filter(\array_keys($classSchema->getTags()), static function ($tagName) use($tag) {
            return $tagName === $tag;
        }));
    }
    /**
     * @return bool
     */
    public function isPropertyTaggedWith($className, $propertyName, $tag)
    {
        $classSchema = $this->getClassSchema($className);
        $property = $classSchema->getProperty($propertyName);
        return empty($property) ? \false : isset($property['tags'][$tag]);
    }
}

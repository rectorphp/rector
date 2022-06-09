<?php

declare (strict_types=1);
namespace RectorPrefix20220609\Symplify\Astral\Reflection;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PHPStan\Reflection\MethodReflection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use RectorPrefix20220609\Symplify\Astral\PhpParser\SmartPhpParser;
use Throwable;
/**
 * @api
 */
final class ReflectionParser
{
    /**
     * @var \Symplify\Astral\PhpParser\SmartPhpParser
     */
    private $smartPhpParser;
    /**
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    public function __construct(SmartPhpParser $smartPhpParser, NodeFinder $nodeFinder)
    {
        $this->smartPhpParser = $smartPhpParser;
        $this->nodeFinder = $nodeFinder;
    }
    public function parsePHPStanMethodReflection(MethodReflection $methodReflection) : ?ClassMethod
    {
        $classReflection = $methodReflection->getDeclaringClass();
        $fileName = $classReflection->getFileName();
        if ($fileName === null) {
            return null;
        }
        $class = $this->parseFilenameToClass($fileName);
        if (!$class instanceof Node) {
            return null;
        }
        return $class->getMethod($methodReflection->getName());
    }
    public function parseMethodReflection(ReflectionMethod $reflectionMethod) : ?ClassMethod
    {
        $class = $this->parseNativeClassReflection($reflectionMethod->getDeclaringClass());
        if (!$class instanceof Class_) {
            return null;
        }
        return $class->getMethod($reflectionMethod->getName());
    }
    public function parsePropertyReflection(ReflectionProperty $reflectionProperty) : ?Property
    {
        $class = $this->parseNativeClassReflection($reflectionProperty->getDeclaringClass());
        if (!$class instanceof Class_) {
            return null;
        }
        return $class->getProperty($reflectionProperty->getName());
    }
    private function parseNativeClassReflection(ReflectionClass $reflectionClass) : ?Class_
    {
        $fileName = $reflectionClass->getFileName();
        if ($fileName === \false) {
            return null;
        }
        return $this->parseFilenameToClass($fileName);
    }
    /**
     * @return \PhpParser\Node\Stmt\Class_|null
     */
    private function parseFilenameToClass(string $fileName)
    {
        try {
            $stmts = $this->smartPhpParser->parseFile($fileName);
        } catch (Throwable $exception) {
            // not reachable
            return null;
        }
        $class = $this->nodeFinder->findFirstInstanceOf($stmts, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        return $class;
    }
}

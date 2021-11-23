<?php

declare (strict_types=1);
namespace RectorPrefix20211123\Symplify\Astral\Reflection;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use ReflectionMethod;
use ReflectionProperty;
use RectorPrefix20211123\Symplify\Astral\PhpParser\SmartPhpParser;
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
    public function __construct(\RectorPrefix20211123\Symplify\Astral\PhpParser\SmartPhpParser $smartPhpParser, \PhpParser\NodeFinder $nodeFinder)
    {
        $this->smartPhpParser = $smartPhpParser;
        $this->nodeFinder = $nodeFinder;
    }
    public function parseMethodReflectionToClassMethod(\ReflectionMethod $reflectionMethod) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $class = $this->parseReflectionToClass($reflectionMethod);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        return $class->getMethod($reflectionMethod->getName());
    }
    public function parsePropertyReflectionToProperty(\ReflectionProperty $reflectionProperty) : ?\PhpParser\Node\Stmt\Property
    {
        $class = $this->parseReflectionToClass($reflectionProperty);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        return $class->getProperty($reflectionProperty->getName());
    }
    /**
     * @param \ReflectionMethod|\ReflectionProperty $reflector
     */
    private function parseReflectionToClass($reflector) : ?\PhpParser\Node\Stmt\Class_
    {
        $reflectionClass = $reflector->getDeclaringClass();
        $fileName = $reflectionClass->getFileName();
        if ($fileName === \false) {
            return null;
        }
        try {
            $stmts = $this->smartPhpParser->parseFile($fileName);
        } catch (\Throwable $exception) {
            // not reachable
            return null;
        }
        $class = $this->nodeFinder->findFirstInstanceOf($stmts, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        return $class;
    }
}

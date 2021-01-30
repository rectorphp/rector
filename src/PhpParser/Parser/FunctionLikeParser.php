<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PHPStan\Reflection\MethodReflection;
use ReflectionFunction;
use Symplify\SmartFileSystem\SmartFileSystem;

final class FunctionLikeParser
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    public function __construct(Parser $parser, SmartFileSystem $smartFileSystem, NodeFinder $nodeFinder)
    {
        $this->parser = $parser;
        $this->smartFileSystem = $smartFileSystem;
        $this->nodeFinder = $nodeFinder;
    }

    public function parseFunction(ReflectionFunction $reflectionFunction): ?Namespace_
    {
        $fileName = $reflectionFunction->getFileName();
        if (! is_string($fileName)) {
            return null;
        }

        $functionCode = $this->smartFileSystem->readFile($fileName);
        if (! is_string($functionCode)) {
            return null;
        }

        $nodes = (array) $this->parser->parse($functionCode);

        $firstNode = $nodes[0] ?? null;
        if (! $firstNode instanceof Namespace_) {
            return null;
        }

        return $firstNode;
    }

    public function parseMethodReflection(MethodReflection $methodReflection): ?ClassMethod
    {
        $classReflection = $methodReflection->getDeclaringClass();

        $fileName = $classReflection->getFileName();
        if (! is_string($fileName)) {
            return null;
        }

        $fileContent = $this->smartFileSystem->readFile($fileName);
        if (! is_string($fileContent)) {
            return null;
        }

        $nodes = (array) $this->parser->parse($fileContent);

        $class = $this->nodeFinder->findFirstInstanceOf($nodes, Class_::class);
        if (! $class instanceof Class_) {
            return null;
        }

        return $class->getMethod($methodReflection->getName());
    }
}

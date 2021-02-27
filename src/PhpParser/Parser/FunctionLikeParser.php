<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PHPStan\Reflection\MethodReflection;
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

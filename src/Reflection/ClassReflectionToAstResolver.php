<?php

declare(strict_types=1);

namespace Rector\Core\Reflection;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Parser;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ClassReflectionToAstResolver
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
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(Parser $parser, SmartFileSystem $smartFileSystem, BetterNodeFinder $betterNodeFinder)
    {
        $this->parser = $parser;
        $this->smartFileSystem = $smartFileSystem;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function getClass(ClassReflection $classReflection, string $className): ?Class_
    {
        /** @var string $fileName */
        $fileName = $classReflection->getFileName();

        /** @var Node[] $contentNodes */
        $contentNodes = $this->parser->parse($this->smartFileSystem->readFile($fileName));
        $classes = $this->betterNodeFinder->findInstanceOf($contentNodes, Class_::class);

        if ($classes === []) {
            return null;
        }

        $reflectionClassName = $classReflection->getName();
        foreach ($classes as $class) {
            $shortClassName = $class->name;
            if ($reflectionClassName === $className) {
                return $class;
            }
        }

        return null;
    }
}

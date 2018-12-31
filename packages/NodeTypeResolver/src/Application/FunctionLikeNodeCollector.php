<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Application;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class FunctionLikeNodeCollector
{
    /**
     * @var ClassMethod[][]
     */
    private $methodsByType = [];

    /**
     * @var Function_[]
     */
    private $functions = [];

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    public function addMethod(ClassMethod $classMethodNode): void
    {
        $className = (string) $classMethodNode->getAttribute(Attribute::CLASS_NAME);
        $methodName = $this->nameResolver->resolve($classMethodNode);

        $this->methodsByType[$className][$methodName] = $classMethodNode;
    }

    public function addFunction(Function_ $functionNode): void
    {
        $functionName = $this->nameResolver->resolve($functionNode);
        $this->functions[$functionName] = $functionNode;
    }

    public function findFunction(string $name): ?Function_
    {
        return $this->functions[$name] ?? null;
    }

    public function findMethod(string $methodName, string $className): ?ClassMethod
    {
        return $this->methodsByType[$className][$methodName] ?? null;
    }

    public function isStaticMethod(string $methodName, string $className): bool
    {
        $methodNode = $this->findMethod($methodName, $className);
        if ($methodNode === null ) {
            return false;
        }

        return $methodNode->isStatic();
    }
}

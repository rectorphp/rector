<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Application;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Resolver\NameResolver;
use ReflectionClass;

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
        $className = $classMethodNode->getAttribute(Attribute::CLASS_NAME);
        if ($className === null) { // anonymous
            return;
        }

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
        if (isset($this->methodsByType[$className][$methodName])) {
            return $this->methodsByType[$className][$methodName];
        }

        $parentClass = $className;
        while ($parentClass = get_parent_class($parentClass)) {
            if (isset($this->methodsByType[$parentClass][$methodName])) {
                return $this->methodsByType[$parentClass][$methodName];
            }
        }

        return null;
    }

    public function isStaticMethod(string $methodName, string $className): bool
    {
        $methodNode = $this->findMethod($methodName, $className);
        if ($methodNode !== null) {
            return $methodNode->isStatic();
        }

        // could be static in doc type magic
        // @see https://regex101.com/r/tlvfTB/1
        if (class_exists($className) || trait_exists($className)) {
            $reflectionClass = new ReflectionClass($className);
            if (Strings::match(
                (string) $reflectionClass->getDocComment(),
                '#@method\s*static\s*(.*?)\b' . $methodName . '\b#'
            )) {
                return true;
            }
        }

        return false;
    }
}

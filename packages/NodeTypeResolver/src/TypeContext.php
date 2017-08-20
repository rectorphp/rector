<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/9373a8e9f551516bc8e42aedeacd1b4f635d27fc/lib/PhpParser/NameContext.php.
 */
final class TypeContext
{
    /**
     * @var mixed[]
     */
    private $types = [];

    /**
     * @var ClassLike|null
     */
    private $classLikeNode;

    /**
     * @var bool
     */
    private $isInClass = false;

    public function startFile(): void
    {
        $this->types = [];
        $this->classLikeNode = [];
        $this->isInClass = false;
    }

    public function addVariableWithType(string $variableName, string $variableType): void
    {
        $this->types[$variableName] = $variableType;
    }

    public function enterClass(ClassLike $classLikeNode): void
    {
        $this->classLikeNode = $classLikeNode;
        $this->isInClass = true;
        // @todo: add properties
    }

    public function enterFunction(FunctionLike $functionLikeNode): void
    {
        $this->types = [];

        $functionReflection = $this->getFunctionReflection($functionLikeNode);
        if ($functionReflection) {
            foreach ($functionReflection->getParameters() as $parameterReflection) {
                $this->types[$parameterReflection->getName()] = $parameterReflection->getType();
            }
        }
    }

    public function getTypeForVariable(string $name): string
    {
        return $this->types[$name] ?? '';
    }

    public function addAssign(string $newVariable, string $oldVariable): void
    {
        $type = $this->getTypeForVariable($oldVariable);
        $this->addVariableWithType($newVariable, $type);
    }

    /**
     * @return ReflectionFunction|ReflectionMethod|null
     */
    private function getFunctionReflection(FunctionLike $functionLikeNode)
    {
        if ($this->classLikeNode) {
            $className = $this->classLikeNode->namespacedName->toString();
            if (! class_exists($className)) {
                return null;
            }

            $methodName = (string) $functionLikeNode->name;

            return new ReflectionMethod($className, $methodName);
        }

        return new ReflectionFunction((string) $functionLikeNode->name);
    }
}

<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionMethod;

/**
 * @see https://3v4l.org/RFYmn
 *
 * @see \Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\MakeInheritedMethodVisibilitySameAsParentRectorTest
 */
final class MakeInheritedMethodVisibilitySameAsParentRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Make method visibility same as parent one', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class ChildClass extends ParentClass
{
    public function run()
    {
    }
}

class ParentClass
{
    protected function run()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class ChildClass extends ParentClass
{
    protected function run()
    {
    }
}

class ParentClass
{
    protected function run()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var Scope|null $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            // possibly trait
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return null;
        }

        /** @var string $methodName */
        $methodName = $this->getName($node->name);

        foreach ($classReflection->getParentClassesNames() as $parentClassName) {
            if (! method_exists($parentClassName, $methodName)) {
                continue;
            }

            $parentReflectionMethod = new ReflectionMethod($parentClassName, $methodName);
            if ($this->isClassMethodCompatibleWithParentReflectionMethod($node, $parentReflectionMethod)) {
                return null;
            }

            if ($this->isConstructorWithStaticFactory($node, $methodName)) {
                return null;
            }

            $this->changeClassMethodVisibilityBasedOnReflectionMethod($node, $parentReflectionMethod);

            return $node;
        }

        return null;
    }

    private function isClassMethodCompatibleWithParentReflectionMethod(
        ClassMethod $classMethod,
        ReflectionMethod $reflectionMethod
    ): bool {
        if ($reflectionMethod->isPublic() && $classMethod->isPublic()) {
            return true;
        }

        if ($reflectionMethod->isProtected() && $classMethod->isProtected()) {
            return true;
        }
        return $reflectionMethod->isPrivate() && $classMethod->isPrivate();
    }

    /**
     * Parent constructor visibility override is allowed only since PHP 7.2+
     * @see https://3v4l.org/RFYmn
     */
    private function isConstructorWithStaticFactory(ClassMethod $classMethod, string $methodName): bool
    {
        if (! $this->isAtLeastPhpVersion('7.2')) {
            return false;
        }

        if ($methodName !== MethodName::CONSTRUCT) {
            return false;
        }

        /** @var Class_|null $classLike */
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return false;
        }

        foreach ($classLike->getMethods() as $iteratedClassMethod) {
            if (! $iteratedClassMethod->isPublic()) {
                continue;
            }

            if (! $iteratedClassMethod->isStatic()) {
                continue;
            }

            $isStaticSelfFactory = $this->isStaticNamedConstructor($iteratedClassMethod);

            if (! $isStaticSelfFactory) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function changeClassMethodVisibilityBasedOnReflectionMethod(
        ClassMethod $classMethod,
        ReflectionMethod $reflectionMethod
    ): void {
        if ($reflectionMethod->isPublic()) {
            $this->makePublic($classMethod);
            return;
        }

        if ($reflectionMethod->isProtected()) {
            $this->makeProtected($classMethod);
            return;
        }

        if ($reflectionMethod->isPrivate()) {
            $this->makePrivate($classMethod);
            return;
        }
    }

    /**
     * Looks for:
     * public static someMethod() { return new self(); }
     * or
     * public static someMethod() { return new static(); }
     */
    private function isStaticNamedConstructor(ClassMethod $classMethod): bool
    {
        if (! $classMethod->isPublic()) {
            return false;
        }

        if (! $classMethod->isStatic()) {
            return false;
        }

        return (bool) $this->betterNodeFinder->findFirst($classMethod, function (Node $node): bool {
            if (! $node instanceof Return_) {
                return false;
            }

            if (! $node->expr instanceof New_) {
                return false;
            }

            return $this->isNames($node->expr->class, ['self', 'static']);
        });
    }
}

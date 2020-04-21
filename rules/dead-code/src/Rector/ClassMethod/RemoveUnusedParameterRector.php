<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\PhpParser\Node\Manipulator\ClassMethodManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DeadCode\NodeManipulator\MagicMethodDetector;
use Rector\DeadCode\NodeManipulator\VariadicFunctionLikeDetector;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://www.php.net/manual/en/function.compact.php
 *
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\RemoveUnusedParameterRectorTest
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\OpenSourceRectorTest
 */
final class RemoveUnusedParameterRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var ClassMethodManipulator
     */
    private $classMethodManipulator;

    /**
     * @var MagicMethodDetector
     */
    private $magicMethodDetector;

    /**
     * @var VariadicFunctionLikeDetector
     */
    private $variadicFunctionLikeDetector;

    public function __construct(
        ClassManipulator $classManipulator,
        ClassMethodManipulator $classMethodManipulator,
        MagicMethodDetector $magicMethodDetector,
        VariadicFunctionLikeDetector $variadicFunctionLikeDetector
    ) {
        $this->classManipulator = $classManipulator;
        $this->classMethodManipulator = $classMethodManipulator;
        $this->magicMethodDetector = $magicMethodDetector;
        $this->variadicFunctionLikeDetector = $variadicFunctionLikeDetector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused parameter, if not required by interface or parent class', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function __construct($value, $value2)
    {
         $this->value = $value;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function __construct($value)
    {
         $this->value = $value;
    }
}
PHP
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var string|null $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }

        $methodName = $this->getName($node);
        if ($this->classManipulator->hasParentMethodOrInterface($className, $methodName)) {
            return null;
        }

        $childrenOfClass = $this->classLikeParsedNodesFinder->findChildrenOfClass($className);
        $unusedParameters = $this->getUnusedParameters($node, $methodName, $childrenOfClass);
        if ($unusedParameters === []) {
            return null;
        }

        foreach ($childrenOfClass as $childClassNode) {
            $methodOfChild = $childClassNode->getMethod($methodName);
            if ($methodOfChild === null) {
                continue;
            }

            $overlappingParameters = $this->getParameterOverlap($methodOfChild->params, $unusedParameters);
            $this->removeNodes($overlappingParameters);
        }

        $this->removeNodes($unusedParameters);

        return $node;
    }

    /**
     * @param Class_[] $childrenOfClass
     * @return Param[]
     */
    private function getUnusedParameters(ClassMethod $classMethod, string $methodName, array $childrenOfClass): array
    {
        $unusedParameters = $this->resolveUnusedParameters($classMethod);
        if ($unusedParameters === []) {
            return [];
        }

        foreach ($childrenOfClass as $childClassNode) {
            $methodOfChild = $childClassNode->getMethod($methodName);
            if ($methodOfChild === null) {
                continue;
            }

            $unusedParameters = $this->getParameterOverlap(
                $unusedParameters,
                $this->resolveUnusedParameters($methodOfChild)
            );
        }

        return $unusedParameters;
    }

    /**
     * @param Param[] $parameters1
     * @param Param[] $parameters2
     * @return Param[]
     */
    private function getParameterOverlap(array $parameters1, array $parameters2): array
    {
        return array_uintersect(
            $parameters1,
            $parameters2,
            function (Param $firstParam, Param $secondParam): int {
                return $this->areNodesEqual($firstParam, $secondParam) ? 0 : 1;
            }
        );
    }

    /**
     * @return Param[]
     */
    private function resolveUnusedParameters(ClassMethod $classMethod): array
    {
        $unusedParameters = [];

        foreach ((array) $classMethod->params as $i => $param) {
            if ($this->classMethodManipulator->isParameterUsedInClassMethod($param, $classMethod)) {
                // reset to keep order of removed arguments, if not construtctor - probably autowired
                if (! $this->isName($classMethod, '__construct')) {
                    $unusedParameters = [];
                }

                continue;
            }

            $unusedParameters[$i] = $param;
        }

        return $unusedParameters;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if ($classMethod->params === []) {
            return true;
        }

        if ($this->magicMethodDetector->isMagicMethod($classMethod)) {
            return true;
        }

        if ($this->variadicFunctionLikeDetector->isVariadic($classMethod)) {
            return true;
        }

        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        // skip interfaces and traits
        if (! $class instanceof Class_) {
            return true;
        }

        if ($this->shouldSkipOpenSourceAbstract($classMethod, $class)) {
            return true;
        }

        if ($this->shouldSkipOpenSourceEmpty($classMethod)) {
            return true;
        }

        if ($this->shouldSkipOpenSourceProtectedMethod($classMethod)) {
            return true;
        }

        return $this->isAnonymousClass($class);
    }

    private function shouldSkipOpenSourceAbstract(ClassMethod $classMethod, Class_ $class): bool
    {
        // skip as possible contract for 3rd party
        if (! $this->isOpenSourceProjectType()) {
            return false;
        }

        if ($classMethod->isAbstract()) {
            return true;
        }

        if (! $class->isAbstract()) {
            return false;
        }

        return $classMethod->isPublic();
    }

    private function shouldSkipOpenSourceEmpty(ClassMethod $classMethod): bool
    {
        // skip as possible contract for 3rd party
        if (! $this->isOpenSourceProjectType()) {
            return false;
        }

        return empty($classMethod->getStmts());
    }

    private function shouldSkipOpenSourceProtectedMethod(ClassMethod $classMethod): bool
    {
        // skip as possible contract for 3rd party
        if (! $this->isOpenSourceProjectType()) {
            return false;
        }

        if ($classMethod->isPublic()) {
            return true;
        }

        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return false;
        }

        if ($class->isFinal()) {
            return false;
        }

        // can be opened
        return $classMethod->isProtected();
    }
}

<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Configuration\Option;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\PhpParser\Node\Manipulator\ClassMethodManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

/**
 * @see https://www.php.net/manual/en/function.compact.php
 *
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\RemoveUnusedParameterRectorTest
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\OpenSourceRectorTest
 */
final class RemoveUnusedParameterRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const MAGIC_METHODS = [
        '__call',
        '__callStatic',
        '__clone',
        '__debugInfo',
        '__destruct',
        '__get',
        '__invoke',
        '__isset',
        '__set',
        '__set_state',
        '__sleep',
        '__toString',
        '__unset',
        '__wakeup',
    ];

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var ClassMethodManipulator
     */
    private $classMethodManipulator;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(
        ClassManipulator $classManipulator,
        ClassMethodManipulator $classMethodManipulator,
        ParameterProvider $parameterProvider
    ) {
        $this->classManipulator = $classManipulator;
        $this->classMethodManipulator = $classMethodManipulator;
        $this->parameterProvider = $parameterProvider;
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
            if ($methodOfChild !== null) {
                $this->removeNodes($this->getParameterOverlap($methodOfChild->params, $unusedParameters));
            }
        }

        $this->removeNodes($unusedParameters);

        return $node;
    }

    /**
     * @param Class_[]    $childrenOfClass
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
            if ($methodOfChild !== null) {
                $unusedParameters = $this->getParameterOverlap(
                    $unusedParameters,
                    $this->resolveUnusedParameters($methodOfChild)
                );
            }
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
            function (Param $a, Param $b): int {
                return $this->areNodesEqual($a, $b) ? 0 : 1;
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
            if ($this->classMethodManipulator->isParameterUsedMethod($param, $classMethod)) {
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

        if ($this->isNames($classMethod, self::MAGIC_METHODS)) {
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

    private function isOpenSourceProjectType(): bool
    {
        $projectType = $this->parameterProvider->provideParameter(Option::PROJECT_TYPE);

        return in_array(
            $projectType,
            [Option::PROJECT_TYPE_OPEN_SOURCE, Option::PROJECT_TYPE_OPEN_SOURCE_UNDESCORED],
            true
        );
    }
}

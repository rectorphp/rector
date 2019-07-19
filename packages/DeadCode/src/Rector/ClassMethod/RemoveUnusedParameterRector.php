<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\Manipulator\ClassMethodManipulator;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://www.php.net/manual/en/function.compact.php
 */
final class RemoveUnusedParameterRector extends AbstractRector
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
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var string[]
     */
    private $magicMethods = [
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
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        ClassManipulator $classManipulator,
        ClassMethodManipulator $classMethodManipulator,
        ParsedNodesByType $parsedNodesByType,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->classManipulator = $classManipulator;
        $this->classMethodManipulator = $classMethodManipulator;
        $this->parsedNodesByType = $parsedNodesByType;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused parameter, if not required by interface or parent class', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct($value, $value2)
    {
         $this->value = $value;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct($value)
    {
         $this->value = $value;
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
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return null;
        }

        if ($node->params === []) {
            return null;
        }

        if ($this->isNames($node, $this->magicMethods)) {
            return null;
        }

        $class = $this->getName($classNode);
        if ($class === null) {
            return null;
        }

        $methodName = $this->getName($node);
        if ($this->classManipulator->hasParentMethodOrInterface($class, $methodName)) {
            return null;
        }

        $childrenOfClass = $this->parsedNodesByType->findChildrenOfClass($class);
        $unusedParameters = $this->getUnusedParameters($node, $methodName, $childrenOfClass);

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
                return $this->betterStandardPrinter->areNodesEqual($a, $b) ? 0 : 1;
            }
        );
    }

    /**
     * @param ClassMethod $classMethod
     * @param string      $methodName
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
}

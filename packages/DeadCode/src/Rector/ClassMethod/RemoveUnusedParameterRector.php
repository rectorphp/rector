<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\Manipulator\ClassMethodManipulator;
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

    public function __construct(ClassManipulator $classManipulator, ClassMethodManipulator $classMethodManipulator)
    {
        $this->classManipulator = $classManipulator;
        $this->classMethodManipulator = $classMethodManipulator;
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

        $unusedParameters = $this->resolveUnusedParameters($node);
        if ($unusedParameters === []) {
            return null;
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
}

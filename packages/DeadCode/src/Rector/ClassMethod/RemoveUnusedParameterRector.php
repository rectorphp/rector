<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Maintainer\ClassMaintainer;
use Rector\PhpParser\Node\Maintainer\ClassMethodMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveUnusedParameterRector extends AbstractRector
{
    /**
     * @var ClassMaintainer
     */
    private $classMaintainer;

    /**
     * @var ClassMethodMaintainer
     */
    private $classMethodMaintainer;

    public function __construct(ClassMaintainer $classMaintainer, ClassMethodMaintainer $classMethodMaintainer)
    {
        $this->classMaintainer = $classMaintainer;
        $this->classMethodMaintainer = $classMethodMaintainer;
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
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return null;
        }

        if ($node->params === []) {
            return null;
        }

        $class = $this->getName($classNode);
        if ($class === null) {
            return null;
        }

        $methodName = $this->getName($node);
        if ($this->classMaintainer->hasParentMethodOrInterface($class, $methodName)) {
            return null;
        }

        $unusedParameters = [];
        foreach ($node->params as $i => $param) {
            if ($this->classMethodMaintainer->isParameterUsedMethod($param, $node)) {
                // reset to keep order of removed arguments
                $unusedParameters = [];
                continue;
            }

            $unusedParameters[$i] = $param;
        }

        foreach ($unusedParameters as $unusedParameter) {
            $this->removeNode($unusedParameter);
        }

        return $node;
    }
}

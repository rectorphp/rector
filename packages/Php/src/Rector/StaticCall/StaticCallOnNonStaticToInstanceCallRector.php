<?php declare(strict_types=1);

namespace Rector\Php\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Application\FunctionLikeNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Maintainer\ClassMethodMaintainer;
use Rector\PhpParser\Node\Maintainer\VisibilityMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use ReflectionClass;

/**
 * @see https://thephp.cc/news/2017/07/dont-call-instance-methods-statically
 * @see https://3v4l.org/tQ32f
 * @see https://3v4l.org/jB9jn
 * @see https://stackoverflow.com/a/19694064/1348344
 */
final class StaticCallOnNonStaticToInstanceCallRector extends AbstractRector
{
    /**
     * @var FunctionLikeNodeCollector
     */
    private $functionLikeNodeCollector;

    /**
     * @var VisibilityMaintainer
     */
    private $visibilityMaintainer;

    /**
     * @var ClassMethodMaintainer
     */
    private $classMethodMaintainer;

    public function __construct(
        FunctionLikeNodeCollector $functionLikeNodeCollector,
        VisibilityMaintainer $visibilityMaintainer,
        ClassMethodMaintainer $classMethodMaintainer
    ) {
        $this->functionLikeNodeCollector = $functionLikeNodeCollector;
        $this->visibilityMaintainer = $visibilityMaintainer;
        $this->classMethodMaintainer = $classMethodMaintainer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes static call to instance call, where not useful', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class Something
{
    public function doWork()
    {
    }
}

class Another
{
    public function run()
    {
        return Something::doWork();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class Something
{
    public function doWork()
    {
    }
}

class Another
{
    public function run()
    {
        return (new Something)->doWork();
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
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $methodName = $this->getName($node);
        $className = $this->getName($node->class);
        if ($methodName === null || $className === null) {
            return null;
        }

        $isStaticMethod = $this->functionLikeNodeCollector->isStaticMethod($methodName, $className);
        if ($isStaticMethod) {
            return null;
        }

        if ($this->isNames($node->class, ['self', 'parent', 'static'])) {
            return null;
        }

        $className = $this->getName($node->class);
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($className === $parentClassName) {
            return null;
        }

        if ($className === null) {
            return null;
        }

        if ($this->isInstantiable($node)) {
            $newNode = new New_($node->class);

            return new MethodCall($newNode, $node->name, $node->args);
        }

        // can we add static to method?

        $classMethodNode = $this->functionLikeNodeCollector->findMethod($methodName, $className);
        if ($classMethodNode === null) {
            return null;
        }

        if ($this->classMethodMaintainer->isStaticClassMethod($classMethodNode)) {
            return null;
        }

        $this->visibilityMaintainer->makeStatic($classMethodNode);

        return null;
    }

    private function isInstantiable(StaticCall $staticCallNode): bool
    {
        $className = $this->getName($staticCallNode->class);

        $reflectionClass = new ReflectionClass($className);
        $classConstructorReflection = $reflectionClass->getConstructor();

        if ($classConstructorReflection === null) {
            return true;
        }

        if ($classConstructorReflection->isPublic() === false) {
            return false;
        }

        // required parameters in constructor, nothing we can do
        return ! (bool) $classConstructorReflection->getNumberOfRequiredParameters();
    }
}

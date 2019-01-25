<?php declare(strict_types=1);

namespace Rector\Php\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeTypeResolver\Application\FunctionLikeNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;
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

    public function __construct(FunctionLikeNodeCollector $functionLikeNodeCollector)
    {
        $this->functionLikeNodeCollector = $functionLikeNodeCollector;
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
        if ($methodName === null) {
            return null;
        }

        $className = $this->getName($node->class);
        if ($className === null) {
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

        if ($this->hasClassConstructorWithRequiredParameters($node)) {
            return null;
        }

        $newNode = new New_($node->class);

        return new MethodCall($newNode, $node->name, $node->args);
    }

    private function hasClassConstructorWithRequiredParameters(StaticCall $staticCallNode): bool
    {
        $className = $this->getName($staticCallNode->class);

        $reflectionClass = new ReflectionClass($className);
        $classConstructorReflection = $reflectionClass->getConstructor();

        if ($classConstructorReflection === null) {
            return false;
        }

        // required parameters in constructor, nothing we can do
        return (bool) $classConstructorReflection->getNumberOfRequiredParameters();
    }
}

<?php declare(strict_types=1);

namespace Rector\Php\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeTypeResolver\Application\FunctionLikeNodeCollector;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use ReflectionClass;

/**
 * @see https://thephp.cc/news/2017/07/dont-call-instance-methods-statically
 * @see https://3v4l.org/tQ32f
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
        $isStaticMethod = $this->functionLikeNodeCollector->isStaticMethod(
            $this->getName($node),
            $this->getName($node->class)
        );
        if ($isStaticMethod) {
            return null;
        }

        if ($this->hasClassConstructorWithRequiredParameters($node)) {
            return null;
        }

        $newNode = new New_($node->class);

        return new MethodCall($newNode, $node->name);
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

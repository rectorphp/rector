<?php declare(strict_types=1);

namespace Rector\Php\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/rkiSC
 * @see \Rector\Php\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\ThisCallOnStaticMethodToStaticCallRectorTest
 */
final class ThisCallOnStaticMethodToStaticCallRector extends AbstractRector
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    public function __construct(ParsedNodesByType $parsedNodesByType)
    {
        $this->parsedNodesByType = $parsedNodesByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes $this->call() to static method to static call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public static function run()
    {
        $this->eat();
    }

    public static function eat()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public static function run()
    {
        self::eat();
    }

    public static function eat()
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->var, 'this')) {
            return null;
        }

        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if (! is_string($className)) {
            return null;
        }

        $methodName = $this->getName($node);
        if ($methodName === null) {
            return null;
        }

        $isStaticMethod = $this->parsedNodesByType->isStaticMethod($methodName, $className);
        if (! $isStaticMethod) {
            return null;
        }

        $methodName = $this->getName($node);
        if ($methodName === null) {
            return null;
        }

        return $this->createStaticCall('self', $methodName, $node->args);
    }
}

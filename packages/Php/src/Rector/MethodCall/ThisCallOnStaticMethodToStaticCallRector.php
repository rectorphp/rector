<?php declare(strict_types=1);

namespace Rector\Php\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\NodeTypeResolver\Application\FunctionLikeNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/rkiSC
 */
final class ThisCallOnStaticMethodToStaticCallRector extends AbstractRector
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

        /** @var string $className */
        $className = $node->getAttribute(Attribute::CLASS_NAME);
        $isStaticMethod = $this->functionLikeNodeCollector->isStaticMethod($this->getName($node), $className);

        if (! $isStaticMethod) {
            return null;
        }

        return new StaticCall(new Name('self'), $this->getName($node));
    }
}

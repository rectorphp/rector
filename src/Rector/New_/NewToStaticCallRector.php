<?php declare(strict_types=1);

namespace Rector\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class NewToStaticCallRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $typeToStaticCalls = [];

    /**
     * @param string[] $typeToStaticCalls
     */
    public function __construct(array $typeToStaticCalls = [])
    {
        $this->typeToStaticCalls = $typeToStaticCalls;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change new Object to static call', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        new Cookie($name);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        Cookie::create($name);
    }
}
CODE_SAMPLE
                ,
                [
                    'Cookie' => [['Cookie', 'create']],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->typeToStaticCalls as $type => $staticCall) {
            if (! $this->isType($node->class, $type)) {
                continue;
            }

            $className = new FullyQualified($staticCall[0]);

            return new StaticCall($className, $staticCall[1], $node->args);
        }

        return $node;
    }
}

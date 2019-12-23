<?php

declare(strict_types=1);

namespace Rector\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\New_\NewToStaticCallRector\NewToStaticCallRectorTest
 */
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
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        new Cookie($name);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        Cookie::create($name);
    }
}
PHP
                ,
                [
                    '$typeToStaticCalls' => [
                        'Cookie' => ['Cookie', 'create'],
                    ],
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
            if (! $this->isObjectType($node->class, $type)) {
                continue;
            }

            return $this->createStaticCall($staticCall[0], $staticCall[1], $node->args);
        }

        return null;
    }
}

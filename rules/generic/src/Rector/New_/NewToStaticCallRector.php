<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\New_\NewToStaticCallRector\NewToStaticCallRectorTest
 */
final class NewToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TYPE_TO_STATIC_CALLS = '$typeToStaticCalls';

    /**
     * @var string[]
     */
    private $typeToStaticCalls = [];

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

    public function configure(array $configuration): void
    {
        $this->typeToStaticCalls = $configuration[self::TYPE_TO_STATIC_CALLS] ?? [];
    }
}

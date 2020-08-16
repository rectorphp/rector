<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\ClassConst\ChangeConstantVisibilityRector\ChangeConstantVisibilityRectorTest
 */
final class ChangeConstantVisibilityRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CONSTANT_TO_VISIBILITY_BY_CLASS = '$constantToVisibilityByClass';

    /**
     * @var string[][] { class => [ method name => visibility ] }
     */
    private $constantToVisibilityByClass = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change visibility of constant from parent class.',
            [new ConfiguredCodeSample(
                <<<'PHP'
class FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}

class MyClass extends FrameworkClass
{
    public const SOME_CONSTANT = 1;
}
PHP
                ,
                <<<'PHP'
class FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}

class MyClass extends FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}
PHP
                ,
                [
                    self::CONSTANT_TO_VISIBILITY_BY_CLASS => [
                        'ParentObject' => [
                            'SOME_CONSTANT' => 'protected',
                        ],
                    ],
                ]
            )]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->constantToVisibilityByClass as $class => $constantsToVisibility) {
            if (! $this->isObjectType($node, $class)) {
                continue;
            }

            foreach ($constantsToVisibility as $constant => $visibility) {
                if (! $this->isName($node, $constant)) {
                    continue;
                }

                $this->changeNodeVisibility($node, $visibility);

                return $node;
            }
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->constantToVisibilityByClass = $configuration[self::CONSTANT_TO_VISIBILITY_BY_CLASS] ?? [];
    }
}

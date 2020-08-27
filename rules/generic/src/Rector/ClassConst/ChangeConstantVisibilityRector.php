<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\ValueObject\ClassConstantVisibilityChange;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\ClassConst\ChangeConstantVisibilityRector\ChangeConstantVisibilityRectorTest
 */
final class ChangeConstantVisibilityRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASS_CONSTANT_VISIBILITY_CHANGES = 'class_constant_visibility_changes';

    /**
     * @var ClassConstantVisibilityChange[]
     */
    private $classConstantVisibilityChanges = [];

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
                    self::CLASS_CONSTANT_VISIBILITY_CHANGES => [
                        new ClassConstantVisibilityChange('ParentObject', 'SOME_CONSTANT', 'protected'),
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
        foreach ($this->classConstantVisibilityChanges as $classConstantVisibilityChange) {
            if (! $this->isObjectType($node, $classConstantVisibilityChange->getClass())) {
                continue;
            }

            if (! $this->isName($node, $classConstantVisibilityChange->getConstant())) {
                continue;
            }

            $this->changeNodeVisibility($node, $classConstantVisibilityChange->getVisibility());

            return $node;
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $classConstantVisibilityChanges = $configuration[self::CLASS_CONSTANT_VISIBILITY_CHANGES] ?? [];
        Assert::allIsInstanceOf($classConstantVisibilityChanges, ClassConstantVisibilityChange::class);
        $this->classConstantVisibilityChanges = $classConstantVisibilityChanges;
    }
}

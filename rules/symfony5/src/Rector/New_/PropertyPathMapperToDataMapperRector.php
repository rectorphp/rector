<?php

declare(strict_types=1);

namespace Rector\Symfony5\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
 * @see \Rector\Symfony5\Tests\Rector\New_\PropertyPathMapperToDataMapperRector\PropertyPathMapperToDataMapperRectorTest
 */
final class PropertyPathMapperToDataMapperRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes first argument of PropertyAccessor::__construct() to flags from boolean', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

class SomeClass
{
    public function run()
    {
        return new PropertyPathMapper();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

class SomeClass
{
    public function run()
    {
        return new \Symfony\Component\Form\Extension\Core\DataMapper\DataMapper(new \Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor());
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
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $arg = null;
        if (isset($node->args[0])) {
            $arg = $node->args[0];
        }

        return $this->generateNewInstances($arg);
    }

    private function shouldSkip(New_ $new_): bool
    {
        if (! $new_->class instanceof Name) {
            return true;
        }

        return ! $this->isName($new_->class, 'Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper');
    }

    private function generateNewInstances(?Arg $arg): New_
    {
        $propertyPathAccessor = new New_(
            new FullyQualified('Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor'),
            [$arg]
        );

        return new New_(
            new FullyQualified('Symfony\Component\Form\Extension\Core\DataMapper\DataMapper'),
            [$this->createArg($propertyPathAccessor)]
        );
    }
}

<?php

declare(strict_types=1);

namespace Rector\Symfony5\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
 * @see \Rector\Symfony5\Tests\Rector\MethodCall\FormBuilderSetDataMapperRector\FormBuilderSetDataMapperRectorTest
 */
final class FormBuilderSetDataMapperRector extends AbstractRector
{
    /**
     * @var string
     */
    private const REQUIRED_TYPE = 'Symfony\Component\Form\FormConfigBuilderInterface';

    /**
     * @var string
     */
    private const ARG_CORRECT_TYPE = 'Symfony\Component\Form\Extension\Core\DataMapper\DataMapper';

    /**
     * @var string
     */
    private const ARG_MAPPER_TYPE = 'Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrates from deprecated Form Builder->setDataMapper(new PropertyPathMapper()) to Builder->setDataMapper(new DataMapper(new PropertyPathAccessor()))',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\FormConfigBuilderInterface;

class SomeClass
{
    public function run(FormConfigBuilderInterface $builder)
    {
        $builder->setDataMapper(new PropertyPathMapper());
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\FormConfigBuilderInterface;

class SomeClass
{
    public function run(FormConfigBuilderInterface $builder)
    {
        $builder->setDataMapper(new \Symfony\Component\Form\Extension\Core\DataMapper\DataMapper(new \Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor()));
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
        if (! $this->isObjectType($node->var, self::REQUIRED_TYPE)) {
            return null;
        }

        if (! $this->isName($node->name, 'setDataMapper')) {
            return null;
        }

        $argumentValue = $node->args[0]->value;
        if ($this->isObjectType($argumentValue, self::ARG_CORRECT_TYPE)) {
            return null;
        }

        $propertyPathAccessor = new New_(new FullyQualified(self::ARG_MAPPER_TYPE));
        $newArgumentValue = new New_(new FullyQualified(self::ARG_CORRECT_TYPE), [new Arg($propertyPathAccessor)]);
        $node->args[0]->value = $newArgumentValue;

        return $node;
    }
}

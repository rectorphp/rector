<?php

declare(strict_types=1);

namespace Rector\Symfony5\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#validator
 * @see \Rector\Symfony5\Tests\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector\ValidatorBuilderEnableAnnotationMappingRectorTest
 */
final class ValidatorBuilderEnableAnnotationMappingRector extends AbstractRector
{
    /**
     * @var string
     */
    private const REQUIRED_TYPE = 'Symfony\Component\Validator\ValidatorBuilder';

    /**
     * @var string
     */
    private const ARG_OLD_TYPE = 'Doctrine\Common\Annotations\Reader';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrates from deprecated ValidatorBuilder->enableAnnotationMapping($reader) to ValidatorBuilder->enableAnnotationMapping(true)->setDoctrineAnnotationReader($reader)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Validator\ValidatorBuilder;

class SomeClass
{
    public function run(ValidatorBuilder $builder, Reader $reader)
    {
        $builder->enableAnnotationMapping($reader);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Validator\ValidatorBuilder;

class SomeClass
{
    public function run(ValidatorBuilder $builder, Reader $reader)
    {
        $builder->enableAnnotationMapping(true)->setDoctrineAnnotationReader($reader);
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

        if (! $this->isName($node->name, 'enableAnnotationMapping')) {
            return null;
        }

        if ($this->valueResolver->isTrueOrFalse($node->args[0]->value)) {
            return null;
        }

        if (! $this->isObjectType($node->args[0]->value, self::ARG_OLD_TYPE)) {
            return null;
        }

        $readerType = $node->args[0]->value;
        $node->args[0]->value = $this->nodeFactory->createTrue();
        return $this->nodeFactory->createMethodCall($node, 'setDoctrineAnnotationReader', [$readerType]);
    }
}

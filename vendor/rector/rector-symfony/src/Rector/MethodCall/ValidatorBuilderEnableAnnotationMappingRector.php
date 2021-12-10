<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#validator
 * @see \Rector\Symfony\Tests\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector\ValidatorBuilderEnableAnnotationMappingRectorTest
 */
final class ValidatorBuilderEnableAnnotationMappingRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrates from deprecated ValidatorBuilder->enableAnnotationMapping($reader) to ValidatorBuilder->enableAnnotationMapping(true)->setDoctrineAnnotationReader($reader)', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Symfony\\Component\\Validator\\ValidatorBuilder'))) {
            return null;
        }
        if (!$this->isName($node->name, 'enableAnnotationMapping')) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        if ($this->valueResolver->isTrueOrFalse($firstArg->value)) {
            return null;
        }
        if (!$this->isObjectType($firstArg->value, new \PHPStan\Type\ObjectType('Doctrine\\Common\\Annotations\\Reader'))) {
            return null;
        }
        $readerType = $firstArg->value;
        $firstArg->value = $this->nodeFactory->createTrue();
        return $this->nodeFactory->createMethodCall($node, 'setDoctrineAnnotationReader', [$readerType]);
    }
}

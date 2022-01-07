<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
 * @see \Rector\Symfony\Tests\Rector\MethodCall\FormBuilderSetDataMapperRector\FormBuilderSetDataMapperRectorTest
 */
final class FormBuilderSetDataMapperRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \PHPStan\Type\ObjectType
     */
    private $dataMapperObjectType;
    public function __construct()
    {
        $this->dataMapperObjectType = new \PHPStan\Type\ObjectType('Symfony\\Component\\Form\\Extension\\Core\\DataMapper\\DataMapper');
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrates from deprecated Form Builder->setDataMapper(new PropertyPathMapper()) to Builder->setDataMapper(new DataMapper(new PropertyPathAccessor()))', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\FormConfigBuilderInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\DataMapper;
use Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor;

class SomeClass
{
    public function run(FormConfigBuilderInterface $builder)
    {
        $builder->setDataMapper(new DataMapper(new PropertyPathAccessor()));
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
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Symfony\\Component\\Form\\FormConfigBuilderInterface'))) {
            return null;
        }
        if (!$this->isName($node->name, 'setDataMapper')) {
            return null;
        }
        $argumentValue = $node->getArgs()[0]->value;
        if ($this->isObjectType($argumentValue, $this->dataMapperObjectType)) {
            return null;
        }
        $propertyPathAccessor = new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\Form\\Extension\\Core\\DataAccessor\\PropertyPathAccessor'));
        $newArgumentValue = new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified($this->dataMapperObjectType->getClassName()), [new \PhpParser\Node\Arg($propertyPathAccessor)]);
        $node->getArgs()[0]->value = $newArgumentValue;
        return $node;
    }
}

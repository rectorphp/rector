<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\FormHelper\FormTypeStringToTypeProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\FormTypeGetParentRector\FormTypeGetParentRectorTest
 */
final class FormTypeGetParentRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Symfony\FormHelper\FormTypeStringToTypeProvider
     */
    private $formTypeStringToTypeProvider;
    public function __construct(\Rector\Symfony\FormHelper\FormTypeStringToTypeProvider $formTypeStringToTypeProvider)
    {
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns string Form Type references to their CONSTANT alternatives in `getParent()` and `getExtendedType()` methods in Form in Symfony', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;

class SomeType extends AbstractType
{
    public function getParent()
    {
        return 'collection';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;

class SomeType extends AbstractType
{
    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\CollectionType::class;
    }
}
CODE_SAMPLE
), new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractTypeExtension;

class SomeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return 'collection';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractTypeExtension;

class SomeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return \Symfony\Component\Form\Extension\Core\Type\CollectionType::class;
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isClassAndMethodMatch($node)) {
            return null;
        }
        $this->traverseNodesWithCallable((array) $node->stmts, function (\PhpParser\Node $node) : ?Node {
            if (!$node instanceof \PhpParser\Node\Stmt\Return_) {
                return null;
            }
            if ($node->expr === null) {
                return null;
            }
            if (!$node->expr instanceof \PhpParser\Node\Scalar\String_) {
                return null;
            }
            $this->replaceStringWIthFormTypeClassConstIfFound($node->expr->value, $node);
            return $node;
        });
        return null;
    }
    private function isClassAndMethodMatch(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $classLike = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        if ($this->isObjectType($classLike, new \PHPStan\Type\ObjectType('Symfony\\Component\\Form\\AbstractType'))) {
            return $this->isName($classMethod->name, 'getParent');
        }
        if ($this->isObjectType($classMethod, new \PHPStan\Type\ObjectType('Symfony\\Component\\Form\\AbstractTypeExtension'))) {
            return $this->isName($classMethod->name, 'getExtendedType');
        }
        return \false;
    }
    private function replaceStringWIthFormTypeClassConstIfFound(string $stringValue, \PhpParser\Node\Stmt\Return_ $return) : void
    {
        $formClass = $this->formTypeStringToTypeProvider->matchClassForNameWithPrefix($stringValue);
        if ($formClass === null) {
            return;
        }
        $return->expr = $this->nodeFactory->createClassConstReference($formClass);
    }
}

<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony30\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\Symfony\FormHelper\FormTypeStringToTypeProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony30\Rector\ClassMethod\FormTypeGetParentRector\FormTypeGetParentRectorTest
 */
final class FormTypeGetParentRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\FormHelper\FormTypeStringToTypeProvider
     */
    private $formTypeStringToTypeProvider;
    public function __construct(FormTypeStringToTypeProvider $formTypeStringToTypeProvider)
    {
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns string Form Type references to their CONSTANT alternatives in `getParent()` and `getExtendedType()` methods in Form in Symfony', [new CodeSample(<<<'CODE_SAMPLE'
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
), new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$this->isClassAndMethodMatch($node, $classMethod)) {
                continue;
            }
            $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use(&$hasChanged) : ?Node {
                if (!$node instanceof Return_) {
                    return null;
                }
                if (!$node->expr instanceof Expr) {
                    return null;
                }
                if (!$node->expr instanceof String_) {
                    return null;
                }
                $this->replaceStringWIthFormTypeClassConstIfFound($node->expr->value, $node, $hasChanged);
                return $node;
            });
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function isClassAndMethodMatch(Class_ $class, ClassMethod $classMethod) : bool
    {
        if ($this->isName($classMethod->name, 'getParent')) {
            return $this->isObjectType($class, new ObjectType('Symfony\\Component\\Form\\AbstractType'));
        }
        if ($this->isName($classMethod->name, 'getExtendedType')) {
            return $this->isObjectType($class, new ObjectType('Symfony\\Component\\Form\\AbstractTypeExtension'));
        }
        return \false;
    }
    private function replaceStringWIthFormTypeClassConstIfFound(string $stringValue, Return_ $return, bool &$hasChanged) : void
    {
        $formClass = $this->formTypeStringToTypeProvider->matchClassForNameWithPrefix($stringValue);
        if ($formClass === null) {
            return;
        }
        $return->expr = $this->nodeFactory->createClassConstReference($formClass);
        $hasChanged = \true;
    }
}

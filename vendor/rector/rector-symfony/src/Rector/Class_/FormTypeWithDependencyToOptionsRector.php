<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Rector\Class_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\Symfony\NodeFactory\FormType\BuildFormOptionAssignsFactory;
use RectorPrefix20220606\Rector\Symfony\NodeRemover\ConstructorDependencyRemover;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://speakerdeck.com/webmozart/symfony-forms-101?slide=24
 *
 * @see \Rector\Symfony\Tests\Rector\Class_\FormTypeWithDependencyToOptionsRector\FormTypeWithDependencyToOptionsRectorTest
 */
final class FormTypeWithDependencyToOptionsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\FormType\BuildFormOptionAssignsFactory
     */
    private $buildFormOptionAssignsFactory;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeRemover\ConstructorDependencyRemover
     */
    private $constructorDependencyRemover;
    public function __construct(BuildFormOptionAssignsFactory $buildFormOptionAssignsFactory, ConstructorDependencyRemover $constructorDependencyRemover)
    {
        $this->buildFormOptionAssignsFactory = $buildFormOptionAssignsFactory;
        $this->constructorDependencyRemover = $constructorDependencyRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Move constructor dependency from form type class to an $options parameter', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class FormTypeWithDependency extends AbstractType
{
    private Agent $agent;

    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($this->agent) {
            $builder->add('agent', TextType::class);
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class FormTypeWithDependency extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $agent = $options['agent'];

        if ($agent) {
            $builder->add('agent', TextType::class);
        }
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
        $formObjectType = new ObjectType('Symfony\\Component\\Form\\AbstractType');
        if (!$this->isObjectType($node, $formObjectType)) {
            return null;
        }
        // skip abstract
        if ($node->isAbstract()) {
            return null;
        }
        $constructorClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof ClassMethod) {
            return null;
        }
        $params = $constructorClassMethod->getParams();
        if ($params === []) {
            return null;
        }
        $buildFormClassMethod = $node->getMethod('buildForm');
        if (!$buildFormClassMethod instanceof ClassMethod) {
            // form has to have some items
            throw new ShouldNotHappenException();
        }
        $paramNames = $this->nodeNameResolver->getNames($params);
        // 1. add assigns at start of ClassMethod
        $assignExpressions = $this->buildFormOptionAssignsFactory->createDimFetchAssignsFromParamNames($paramNames);
        $buildFormClassMethod->stmts = \array_merge($assignExpressions, (array) $buildFormClassMethod->stmts);
        // 2. remove properties
        foreach ($node->getProperties() as $property) {
            if (!$this->isNames($property, $paramNames)) {
                continue;
            }
            $this->removeNode($property);
        }
        // 3. cleanup ctor
        $this->constructorDependencyRemover->removeParamsByName($constructorClassMethod, $paramNames);
        $this->replacePropertyFetchesByVariables($buildFormClassMethod, $paramNames);
        return $node;
    }
    /**
     * 4. replace property fetches in buildForm() by just assigned variable
     *
     * @param string[] $paramNames
     */
    private function replacePropertyFetchesByVariables(ClassMethod $classMethod, array $paramNames) : void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use($paramNames) : ?Variable {
            if (!$node instanceof PropertyFetch) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->var, 'this')) {
                return null;
            }
            if (!$this->nodeNameResolver->isNames($node->name, $paramNames)) {
                return null;
            }
            // replace by variable
            $variableName = $this->getName($node->name);
            if (!\is_string($variableName)) {
                return null;
            }
            return new Variable($variableName);
        });
    }
}

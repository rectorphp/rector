<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Symfony\NodeFactory\FormType\BuildFormOptionAssignsFactory;
use Rector\Symfony\NodeFactory\FormType\ConfigureDefaultsFactory;
use Rector\Symfony\NodeRemover\ConstructorDependencyRemover;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\FormType\ConfigureDefaultsFactory
     */
    private $configureDefaultsFactory;
    public function __construct(BuildFormOptionAssignsFactory $buildFormOptionAssignsFactory, ConstructorDependencyRemover $constructorDependencyRemover, ConfigureDefaultsFactory $configureDefaultsFactory)
    {
        $this->buildFormOptionAssignsFactory = $buildFormOptionAssignsFactory;
        $this->constructorDependencyRemover = $constructorDependencyRemover;
        $this->configureDefaultsFactory = $configureDefaultsFactory;
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
        // skip abstract
        if ($node->isAbstract()) {
            return null;
        }
        $formObjectType = new ObjectType('Symfony\\Component\\Form\\AbstractType');
        if (!$this->isObjectType($node, $formObjectType)) {
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
            return null;
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
        // 4. add configure options class method
        $classMethod = $this->configureDefaultsFactory->create($paramNames);
        $node->stmts[] = $classMethod;
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

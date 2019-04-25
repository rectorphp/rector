<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Class_;

use PhpParser\Node;
use Rector\Naming\PropertyNaming;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class StaticTypeToSetterInjectionRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $staticTypes = [];

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @param string[] $staticTypes
     */
    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        PropertyNaming $propertyNaming,
        array $staticTypes = []
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->propertyNaming = $propertyNaming;
        $this->staticTypes = $staticTypes;
    }

    public function getDefinition(): RectorDefinition
    {
        // custom made only for Elasticr
        return new RectorDefinition('Changes types to setter injection', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
<?php

final class CheckoutEntityFactory
{
    public function run()
    {
        return SomeStaticClass::go();
    }
}
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
<?php

final class CheckoutEntityFactory
{
    /**
     * @var SomeStaticClass
     */
    private $someStaticClass;
    
    public function setSomeStaticClass(SomeStaticClass $someStaticClass)
    {
        $this->someStaticClass = $someStaticClass;
    }
    
    public function run()
    {
        return $this->someStaticClass->go();
    }
}               
CODE_SAMPLE
                ,
                ['$staticTypes' => ['SomeStaticClass']]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Node\Stmt\Class_::class, Node\Expr\StaticCall::class];
    }

    /**
     * @param Node\Expr\StaticCall|Node\Stmt\Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Node\Stmt\Class_) {
            return $this->processClass($node);
        }

        foreach ($this->staticTypes as $staticType) {
            if (! $this->isType($node, $staticType)) {
                continue;
            }

            $variableName = $this->propertyNaming->fqnToVariableName($staticType);
            $propertyFetch = new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $variableName);

            return new Node\Expr\MethodCall($propertyFetch, $node->name, $node->args);
        }

        return null;
    }

    private function isEntityFactoryStaticCall(Node $node): bool
    {
        if (! $node instanceof Node\Expr\StaticCall) {
            return false;
        }

        return $this->isTypes($node, $this->staticTypes);
    }

    private function processClass(Node\Stmt\Class_ $class): ?Node\Stmt\Class_
    {
        foreach ($this->staticTypes as $staticType) {
            $containsEntityFactoryStaticCall = (bool) $this->betterNodeFinder->findFirst(
                $class->stmts,
                function (Node $node) {
                    return $this->isEntityFactoryStaticCall($node);
                }
            );

            if (! $containsEntityFactoryStaticCall) {
                continue;
            }

            // @todo resolve in configuration: key => value
            // $class->implements[] = new Node\Name\FullyQualified($this->entityFactoryAwareInterface);

            $variableName = $this->propertyNaming->fqnToVariableName($staticType);

            $param = $this->builderFactory->param($variableName)
                ->setType(new Node\Name\FullyQualified($staticType))
                ->getNode();

            $propertyFetch = new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $variableName);
            $assign = new Node\Expr\Assign($propertyFetch, new Node\Expr\Variable($variableName));

            $setMethodName = 'set' . ucfirst($variableName);

            $setEntityFactoryMethod = $this->builderFactory->method($setMethodName)
                ->setReturnType('void')
                ->addParam($param)
                ->addStmt($assign)
                ->makePublic()
                ->getNode();

            $entityFactoryProperty = $this->builderFactory->property($variableName)
                ->makePrivate()
                ->getNode();

            $this->docBlockManipulator->addVarTag($entityFactoryProperty, '\\' . $staticType);

            $class->stmts = array_merge([$entityFactoryProperty, $setEntityFactoryMethod], $class->stmts);

            break;
        }

        return $class;
    }
}

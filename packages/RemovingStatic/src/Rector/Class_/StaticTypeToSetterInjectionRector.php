<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
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
        return [Class_::class, StaticCall::class];
    }

    /**
     * @param StaticCall|Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Class_) {
            return $this->processClass($node);
        }

        foreach ($this->staticTypes as $staticType) {
            if (! $this->isType($node->class, $staticType)) {
                continue;
            }

            $variableName = $this->propertyNaming->fqnToVariableName($staticType);
            $propertyFetch = new PropertyFetch(new Variable('this'), $variableName);

            return new MethodCall($propertyFetch, $node->name, $node->args);
        }

        return null;
    }

    private function isEntityFactoryStaticCall(Node $node, string $staticType): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        return $this->isType($node->class, $staticType);
    }

    private function processClass(Class_ $class): ?Class_
    {
        foreach ($this->staticTypes as $implements => $staticType) {
            $containsEntityFactoryStaticCall = (bool) $this->betterNodeFinder->findFirst(
                $class->stmts,
                function (Node $node) use ($staticType) {
                    return $this->isEntityFactoryStaticCall($node, $staticType);
                }
            );

            if (! $containsEntityFactoryStaticCall) {
                continue;
            }

            if (is_string($implements)) {
                $class->implements[] = new FullyQualified($implements);
            }

            $variableName = $this->propertyNaming->fqnToVariableName($staticType);

            $param = $this->builderFactory->param($variableName)
                ->setType(new FullyQualified($staticType))
                ->getNode();

            $propertyFetch = new PropertyFetch(new Variable('this'), $variableName);
            $assign = new Assign($propertyFetch, new Variable($variableName));

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

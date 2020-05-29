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
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Naming\PropertyNaming;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\StaticTypeToSetterInjectionRectorTest
 */
final class StaticTypeToSetterInjectionRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $staticTypes = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @param string[] $staticTypes
     */
    public function __construct(PropertyNaming $propertyNaming, array $staticTypes = [])
    {
        $this->propertyNaming = $propertyNaming;
        $this->staticTypes = $staticTypes;
    }

    public function getDefinition(): RectorDefinition
    {
        // custom made only for Elasticr
        return new RectorDefinition('Changes types to setter injection', [
            new ConfiguredCodeSample(
<<<'PHP'
<?php

final class CheckoutEntityFactory
{
    public function run()
    {
        return SomeStaticClass::go();
    }
}
PHP
                ,
<<<'PHP'
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
PHP
                ,
                [
                    '$staticTypes' => ['SomeStaticClass'],
                ]
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
            $objectType = new ObjectType($staticType);
            if (! $this->isObjectType($node->class, $objectType)) {
                continue;
            }

            $variableName = $this->propertyNaming->fqnToVariableName($objectType);
            $propertyFetch = new PropertyFetch(new Variable('this'), $variableName);

            return new MethodCall($propertyFetch, $node->name, $node->args);
        }

        return null;
    }

    private function processClass(Class_ $class): Class_
    {
        foreach ($this->staticTypes as $implements => $staticType) {
            $objectType = new ObjectType($staticType);

            $containsEntityFactoryStaticCall = (bool) $this->betterNodeFinder->findFirst(
                $class->stmts,
                function (Node $node) use ($objectType): bool {
                    return $this->isEntityFactoryStaticCall($node, $objectType);
                }
            );

            if (! $containsEntityFactoryStaticCall) {
                continue;
            }

            if (is_string($implements)) {
                $class->implements[] = new FullyQualified($implements);
            }

            $variableName = $this->propertyNaming->fqnToVariableName($objectType);

            $paramBuilder = $this->builderFactory->param($variableName);
            $paramBuilder->setType(new FullyQualified($staticType));
            $param = $paramBuilder->getNode();

            $assign = $this->nodeFactory->createPropertyAssignment($variableName);

            $setEntityFactoryMethod = $this->createSetEntityFactoryClassMethod($variableName, $param, $assign);

            $entityFactoryProperty = $this->nodeFactory->createPrivateProperty($variableName);

            /** @var PhpDocInfo $phpDocInfo */
            $phpDocInfo = $entityFactoryProperty->getAttribute(AttributeKey::PHP_DOC_INFO);
            $phpDocInfo->changeVarType($objectType);

            $class->stmts = array_merge([$entityFactoryProperty, $setEntityFactoryMethod], $class->stmts);

            break;
        }

        return $class;
    }

    private function isEntityFactoryStaticCall(Node $node, ObjectType $objectType): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        return $this->isObjectType($node->class, $objectType);
    }

    private function createSetEntityFactoryClassMethod(
        string $variableName,
        Param $param,
        Assign $assign
    ): ClassMethod {
        $setMethodName = 'set' . ucfirst($variableName);

        $setEntityFactoryMethodBuilder = $this->builderFactory->method($setMethodName);
        $setEntityFactoryMethodBuilder->makePublic();
        $setEntityFactoryMethodBuilder->addParam($param);
        $setEntityFactoryMethodBuilder->setReturnType('void');
        $setEntityFactoryMethodBuilder->addStmt($assign);

        return $setEntityFactoryMethodBuilder->getNode();
    }
}

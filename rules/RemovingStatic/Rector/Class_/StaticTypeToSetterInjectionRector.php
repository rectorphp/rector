<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector\StaticTypeToSetterInjectionRectorTest
 */
final class StaticTypeToSetterInjectionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const STATIC_TYPES = 'static_types';

    /**
     * @var array<class-string|int, class-string>
     */
    private array $staticTypes = [];

    public function __construct(
        private PropertyNaming $propertyNaming,
        private PhpDocTypeChanger $phpDocTypeChanger
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        // custom made only for Elasticr
        return new RuleDefinition('Changes types to setter injection', [
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
                [
                    self::STATIC_TYPES => ['SomeStaticClass'],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
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

    /**
     * @param array<string, array<class-string|int, class-string>> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->staticTypes = $configuration[self::STATIC_TYPES] ?? [];
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
            $setEntityFactoryMethod = $this->nodeFactory->createSetterClassMethod($variableName, $objectType);

            $entityFactoryProperty = $this->nodeFactory->createPrivateProperty($variableName);

            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($entityFactoryProperty);
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $objectType);

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
}

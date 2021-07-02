<?php

declare (strict_types=1);
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
final class StaticTypeToSetterInjectionRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const STATIC_TYPES = 'static_types';
    /**
     * @var array<class-string|int, class-string>
     */
    private $staticTypes = [];
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    public function __construct(\Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->propertyNaming = $propertyNaming;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        // custom made only for Elasticr
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes types to setter injection', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class CheckoutEntityFactory
{
    public function run()
    {
        return SomeStaticClass::go();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
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
, [self::STATIC_TYPES => ['SomeStaticClass']])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall|Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\Class_) {
            return $this->processClass($node);
        }
        foreach ($this->staticTypes as $staticType) {
            $objectType = new \PHPStan\Type\ObjectType($staticType);
            if (!$this->isObjectType($node->class, $objectType)) {
                continue;
            }
            $variableName = $this->propertyNaming->fqnToVariableName($objectType);
            $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $variableName);
            return new \PhpParser\Node\Expr\MethodCall($propertyFetch, $node->name, $node->args);
        }
        return null;
    }
    /**
     * @param array<string, array<class-string|int, class-string>> $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->staticTypes = $configuration[self::STATIC_TYPES] ?? [];
    }
    private function processClass(\PhpParser\Node\Stmt\Class_ $class) : \PhpParser\Node\Stmt\Class_
    {
        foreach ($this->staticTypes as $implements => $staticType) {
            $objectType = new \PHPStan\Type\ObjectType($staticType);
            $containsEntityFactoryStaticCall = (bool) $this->betterNodeFinder->findFirst($class->stmts, function (\PhpParser\Node $node) use($objectType) : bool {
                return $this->isEntityFactoryStaticCall($node, $objectType);
            });
            if (!$containsEntityFactoryStaticCall) {
                continue;
            }
            if (\is_string($implements)) {
                $class->implements[] = new \PhpParser\Node\Name\FullyQualified($implements);
            }
            $variableName = $this->propertyNaming->fqnToVariableName($objectType);
            $setEntityFactoryMethod = $this->nodeFactory->createSetterClassMethod($variableName, $objectType);
            $entityFactoryProperty = $this->nodeFactory->createPrivateProperty($variableName);
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($entityFactoryProperty);
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $objectType);
            $class->stmts = \array_merge([$entityFactoryProperty, $setEntityFactoryMethod], $class->stmts);
            break;
        }
        return $class;
    }
    private function isEntityFactoryStaticCall(\PhpParser\Node $node, \PHPStan\Type\ObjectType $objectType) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\StaticCall) {
            return \false;
        }
        return $this->isObjectType($node->class, $objectType);
    }
}

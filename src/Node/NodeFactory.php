<?php declare(strict_types=1);

namespace Rector\Node;

use PhpParser\BuilderFactory;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\TraitUse;
use Rector\Exception\NotImplementedException;

final class NodeFactory
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var PropertyFetchNodeFactory
     */
    private $propertyFetchNodeFactory;

    public function __construct(BuilderFactory $builderFactory, PropertyFetchNodeFactory $propertyFetchNodeFactory)
    {
        $this->builderFactory = $builderFactory;
        $this->propertyFetchNodeFactory = $propertyFetchNodeFactory;
    }

    /**
     * Creates "null"
     */
    public function createNullConstant(): ConstFetch
    {
        return BuilderHelpers::normalizeValue(null);
    }

    /**
     * Creates "false"
     */
    public function createFalseConstant(): ConstFetch
    {
        return BuilderHelpers::normalizeValue(false);
    }

    /**
     * Creates "true"
     */
    public function createTrueConstant(): ConstFetch
    {
        return BuilderHelpers::normalizeValue(true);
    }

    /**
     * Creates "SomeClass::CONSTANT"
     */
    public function createClassConstant(string $className, string $constantName): ClassConstFetch
    {
        $classNameNode = new FullyQualified($className);

        return new ClassConstFetch($classNameNode, $constantName);
    }

    /**
     * Creates "SomeClass::class"
     */
    public function createClassConstantReference(string $className): ClassConstFetch
    {
        $nameNode = new FullyQualified($className);

        return new ClassConstFetch($nameNode, 'class');
    }

    /**
     * Creates "use \SomeTrait;"
     */
    public function createTraitUse(string $traitName): TraitUse
    {
        $traitNameNode = new FullyQualified($traitName);

        return new TraitUse([$traitNameNode]);
    }

    /**
     * Creates "['item', $variable]"
     *
     * @param mixed|Node[] ...$items
     */
    public function createArray(...$items): Array_
    {
        $arrayItems = [];

        foreach ($items as $item) {
            if ($item instanceof Variable) {
                $arrayItems[] = new ArrayItem($item);
            } elseif ($item instanceof Identifier) {
                $string = new String_($item->toString());
                $arrayItems[] = new ArrayItem($string);
            } else {
                throw new NotImplementedException(sprintf(
                    'Not implemented yet. Go to "%s()" and add check for "%s" node.',
                    __METHOD__,
                    get_class($item)
                ));
            }
        }

        return new Array_($arrayItems, [
            'kind' => Array_::KIND_SHORT,
        ]);
    }

    /**
     * Creates "($args)"
     *
     * @param mixed[] $arguments
     * @return Arg[]
     */
    public function createArgs(array $arguments): array
    {
        return $this->builderFactory->args($arguments);
    }

    /**
     * Creates $this->property = $property;
     */
    public function createPropertyAssignment(string $propertyName): Expression
    {
        $variable = new Variable($propertyName, [
            'name' => $propertyName,
        ]);

        $assign = new Assign(
            $this->propertyFetchNodeFactory->createLocalWithPropertyName($propertyName),
            $variable
        );

        return new Expression($assign);
    }

    /**
     * Creates $this->values[] = $value;
     */
    public function createPropertyArrayAssignment(string $propertyName, string $argumentName): Expression
    {
        $variable = new Variable($argumentName, [
            'name' => $argumentName,
        ]);

        $assign = new Assign(
            $this->propertyFetchNodeFactory->createLocalArrayFetchWithPropertyName($propertyName),
            $variable
        );

        return new Expression($assign);
    }

    /**
     * @param mixed $argument
     */
    public function createArg($argument): Arg
    {
        $value = BuilderHelpers::normalizeValue($argument);

        return new Arg($value);
    }

    public function createDeclareStrictTypes(): Declare_
    {
        return new Declare_([
            new DeclareDeclare(
                new Identifier('strict_types'),
                new LNumber(1)
            ),
        ]);
    }

    /**
     * Creates:
     * - $variable->property['key'];
     */
    public function createVariablePropertyArrayFetch(
        Expr $exprNode,
        string $propertyName,
        String_ $keyNode
    ): ArrayDimFetch {
        return new ArrayDimFetch(
            new PropertyFetch($exprNode, new Identifier($propertyName)),
            $keyNode
        );
    }

    /**
     * Creates:
     * - namespace NamespaceName;
     */
    public function createNamespace(string $namespace): Namespace_
    {
        return $this->builderFactory->namespace($namespace)
            ->getNode();
    }

    public function createParam(string $name, string $type): Param
    {
        return $this->builderFactory->param($name)
            ->setTypeHint($type)
            ->getNode();
    }

    public function createVariable(string $name): Variable
    {
        return new Variable($name);
    }

    /**
     * @param mixed[] $arguments
     */
    public function createStaticMethodCallWithArgs(string $class, string $method, array $arguments): StaticCall
    {
        return new StaticCall(new Name($class), new Identifier($method), $arguments);
    }
}

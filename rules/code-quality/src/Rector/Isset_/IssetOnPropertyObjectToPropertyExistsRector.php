<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Isset_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\IssetOnPropertyObjectToPropertyExistsRectorTest
 * @see https://3v4l.org/TI8XL Change isset on property object to property_exists() with not null check
 */
final class IssetOnPropertyObjectToPropertyExistsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change isset on property object to property_exists()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    private $x;

    public function run(): void
    {
        isset($this->x);
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    private $x;

    public function run(): void
    {
        property_exists($this, 'x') && $this->x !== null;
    }
}
CODE_SAMPLE
            ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Isset_::class];
    }

    /**
     * @param Isset_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $newNodes = [];

        foreach ($node->vars as $issetVar) {
            if (! $issetVar instanceof PropertyFetch) {
                continue;
            }

            /** @var Expr $object */
            $object = $issetVar->var->getAttribute(AttributeKey::ORIGINAL_NODE);

            /** @var Scope $scope */
            $scope = $object->getAttribute(AttributeKey::SCOPE);
            /** @var ThisType|ObjectType $type */
            $type = $scope->getType($object);

            if ($type instanceof ThisType) {
                $newNodes[] = new NotIdentical($issetVar, $this->createNull());
                continue;
            }

            /** @var Identifier $name */
            $name = $issetVar->name;
            $property = $name->toString();

            if ($type instanceof ObjectType) {
                /** @var string $className */
                $className = $type->getClassName();

                $isPropertyAlwaysExists = property_exists($className, $property);
                if ($isPropertyAlwaysExists) {
                    $newNodes[] = new NotIdentical($issetVar, $this->createNull());
                    continue;
                }
            }

            $newNodes[] = $this->replaceToPropertyExistsWithNullCheck($object, $property, $issetVar);
        }

        return $this->createReturnNodes($newNodes);
    }

    private function replaceToPropertyExistsWithNullCheck(Expr $expr, string $property, Expr $issetVar): BooleanAnd
    {
        $args = [new Arg($expr), new Arg(new String_($property))];
        $propertyExistsFuncCall = new FuncCall(new Name('property_exists'), $args);

        return new BooleanAnd($propertyExistsFuncCall, new NotIdentical($issetVar, $this->createNull()));
    }

    /**
     * @param NotIdentical[]|BooleanAnd[] $newNodes
     */
    private function createReturnNodes(array $newNodes): ?Expr
    {
        if ($newNodes === []) {
            return null;
        }

        if (count($newNodes) === 1) {
            return $newNodes[0];
        }

        return $this->createBooleanAndFromNodes($newNodes);
    }

    /**
     * @param NotIdentical[]|BooleanAnd[] $exprs
     * @todo decouple to StackNodeFactory
     */
    private function createBooleanAndFromNodes(array $exprs): BooleanAnd
    {
        /** @var NotIdentical|BooleanAnd $booleanAnd */
        $booleanAnd = array_shift($exprs);
        foreach ($exprs as $expr) {
            $booleanAnd = new BooleanAnd($booleanAnd, $expr);
        }

        /** @var BooleanAnd $booleanAnd */
        return $booleanAnd;
    }
}

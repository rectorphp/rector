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
        foreach ($node->vars as $issetVar) {
            if (! $issetVar instanceof PropertyFetch) {
                continue;
            }

            $previous = $issetVar->getAttribute(AttributeKey::PREVIOUS_NODE);
            $next = $issetVar->getAttribute(AttributeKey::NEXT_NODE);

            if ($this->isFoundInPreviuosOrNext($previous, $next, $node)) {
                continue;
            }

            /** @var Expr $object */
            $object = $issetVar->var->getAttribute(AttributeKey::ORIGINAL_NODE);

            /** @var Scope $scope */
            $scope = $object->getAttribute(AttributeKey::SCOPE);
            /** @var ThisType|ObjectType $type */
            $type = $scope->getType($object);

            if ($type instanceof ThisType) {
                return new NotIdentical($issetVar, $this->createNull());
            }

            /** @var Identifier $name */
            $name = $issetVar->name;
            $property = $name->toString();

            if ($type instanceof ObjectType) {
                /** @var string $className */
                $className = $type->getClassName();

                $isPropertyAlwaysExists = property_exists($className, $property);
                if ($isPropertyAlwaysExists) {
                    return null;
                }
            }

            return $this->replaceToPropertyExistsWithNullCheck($object, $property, $issetVar);
        }

        return null;
    }

    /**
     * @param Node $previous
     * @param Node $next
     */
    private function isFoundInPreviuosOrNext(?Node $previous = null, ?Node $next = null, Isset_ $isset): bool
    {
        if ($previous && $previous->getAttribute(AttributeKey::PARENT_NODE) === $isset) {
            return true;
        }
        return $next && $next->getAttribute(AttributeKey::PARENT_NODE) === $isset;
    }

    private function replaceToPropertyExistsWithNullCheck(Expr $expr, string $property, Expr $issetVar): BooleanAnd
    {
        $args = [new Arg($expr), new Arg(new String_($property))];
        $propertyExistsFuncCall = new FuncCall(new Name('property_exists'), $args);

        return new BooleanAnd($propertyExistsFuncCall, new NotIdentical($issetVar, $this->createNull()));
    }
}

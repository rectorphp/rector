<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\TypeAnalyzer;

use Countable;
use PhpParser\Node;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeCorrector\PregMatchTypeCorrector;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class CountableTypeAnalyzer
{
    /**
     * @var ArrayTypeAnalyzer
     */
    private $arrayTypeAnalyzer;

    /**
     * @var PregMatchTypeCorrector
     */
    private $pregMatchTypeCorrector;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        ArrayTypeAnalyzer $arrayTypeAnalyzer,
        NodeTypeResolver $nodeTypeResolver,
        PregMatchTypeCorrector $pregMatchTypeCorrector
    ) {
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
        $this->pregMatchTypeCorrector = $pregMatchTypeCorrector;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function isCountableType(Node $node): bool
    {
        $nodeType = $this->nodeTypeResolver->resolve($node);
        $nodeType = $this->pregMatchTypeCorrector->correct($node, $nodeType);

        if ($this->isCountableObjectType($nodeType)) {
            return true;
        }

        return $this->arrayTypeAnalyzer->isArrayType($node);
    }

    private function isCountableObjectType(Type $type): bool
    {
        $countableObjectTypes = [
            new ObjectType(Countable::class),
            new ObjectType('SimpleXMLElement'),
            new ObjectType('ResourceBundle'),
        ];

        if ($type instanceof UnionType) {
            return $this->isCountableUnionType($type, $countableObjectTypes);
        }

        if ($type instanceof ObjectType) {
            foreach ($countableObjectTypes as $countableObjectType) {
                if (! is_a($type->getClassName(), $countableObjectType->getClassName(), true)) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param ObjectType[] $countableObjectTypes
     */
    private function isCountableUnionType(UnionType $unionType, array $countableObjectTypes): bool
    {
        if ($unionType->isSubTypeOf(new NullType())->yes()) {
            return false;
        }

        foreach ($countableObjectTypes as $countableObjectType) {
            if ($unionType->isSuperTypeOf($countableObjectType)->yes()) {
                return true;
            }
        }

        return false;
    }
}

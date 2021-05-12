<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\TypeAnalyzer;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeCorrector\PregMatchTypeCorrector;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class CountableTypeAnalyzer
{
    /**
     * @var ObjectType[]
     */
    private array $countableObjectTypes = [];

    public function __construct(
        private ArrayTypeAnalyzer $arrayTypeAnalyzer,
        private NodeTypeResolver $nodeTypeResolver,
        private PregMatchTypeCorrector $pregMatchTypeCorrector
    ) {
        $this->countableObjectTypes = [
            new ObjectType('Countable'),
            new ObjectType('SimpleXMLElement'),
            new ObjectType('ResourceBundle'),
        ];
    }

    public function isCountableType(Node $node): bool
    {
        $nodeType = $this->nodeTypeResolver->resolve($node);
        $nodeType = $this->pregMatchTypeCorrector->correct($node, $nodeType);

        foreach ($this->countableObjectTypes as $countableObjectType) {
            if ($countableObjectType->isSuperTypeOf($nodeType)->yes()) {
                return true;
            }
        }

        return $this->arrayTypeAnalyzer->isArrayType($node);
    }
}

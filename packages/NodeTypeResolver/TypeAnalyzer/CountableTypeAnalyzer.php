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

    /**
     * @var ObjectType[]
     */
    private $countableObjectTypes = [];

    public function __construct(
        ArrayTypeAnalyzer $arrayTypeAnalyzer,
        NodeTypeResolver $nodeTypeResolver,
        PregMatchTypeCorrector $pregMatchTypeCorrector
    ) {
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
        $this->pregMatchTypeCorrector = $pregMatchTypeCorrector;
        $this->nodeTypeResolver = $nodeTypeResolver;

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

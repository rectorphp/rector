<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\TypeAnalyzer;

use PhpParser\Node\Expr;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class CountableTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer
     */
    private $arrayTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var ObjectType[]
     */
    private $countableObjectTypes = [];
    public function __construct(\Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer $arrayTypeAnalyzer, NodeTypeResolver $nodeTypeResolver)
    {
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->countableObjectTypes = [new ObjectType('Countable'), new ObjectType('SimpleXMLElement'), new ObjectType('ResourceBundle')];
    }
    public function isCountableType(Expr $expr) : bool
    {
        $nodeType = $this->nodeTypeResolver->getType($expr);
        foreach ($this->countableObjectTypes as $countableObjectType) {
            if ($countableObjectType->isSuperTypeOf($nodeType)->yes()) {
                return \true;
            }
        }
        return $this->arrayTypeAnalyzer->isArrayType($expr);
    }
}

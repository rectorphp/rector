<?php

declare (strict_types=1);
namespace Rector\Symfony\TypeAnalyzer;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ContainerAwareAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var ObjectType[]
     */
    private $getMethodAwareObjectTypes = [];
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->getMethodAwareObjectTypes = [new ObjectType('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController'), new ObjectType('Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller'), new ObjectType('Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerTrait')];
    }
    public function isGetMethodAwareType(Node $node) : bool
    {
        return $this->nodeTypeResolver->isObjectTypes($node, $this->getMethodAwareObjectTypes);
    }
}

<?php

declare (strict_types=1);
namespace Rector\Symfony\TypeAnalyzer;

use PhpParser\Node\Expr;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ContainerAwareAnalyzer
{
    /**
     * @var ObjectType[]
     */
    private $getMethodAwareObjectTypes = [];
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->getMethodAwareObjectTypes = [new \PHPStan\Type\ObjectType('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController'), new \PHPStan\Type\ObjectType('Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller'), new \PHPStan\Type\ObjectType('Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerTrait')];
    }
    public function isGetMethodAwareType(\PhpParser\Node\Expr $expr) : bool
    {
        return $this->nodeTypeResolver->isObjectTypes($expr, $this->getMethodAwareObjectTypes);
    }
}

<?php

declare (strict_types=1);
namespace Rector\Symfony\TypeAnalyzer;

use PhpParser\Node\Expr;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Symfony\Enum\SymfonyClass;
final class ContainerAwareAnalyzer
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @var ObjectType[]
     */
    private array $getMethodAwareObjectTypes = [];
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->getMethodAwareObjectTypes = [new ObjectType(SymfonyClass::ABSTRACT_CONTROLLER), new ObjectType(SymfonyClass::CONTROLLER), new ObjectType(SymfonyClass::CONTROLLER_TRAIT)];
    }
    public function isGetMethodAwareType(Expr $expr) : bool
    {
        return $this->nodeTypeResolver->isObjectTypes($expr, $this->getMethodAwareObjectTypes);
    }
}

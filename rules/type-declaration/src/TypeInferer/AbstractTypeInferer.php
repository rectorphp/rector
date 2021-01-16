<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

abstract class AbstractTypeInferer
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    protected $simpleCallableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @var StaticTypeMapper
     */
    protected $staticTypeMapper;

    /**
     * @var TypeFactory
     */
    protected $typeFactory;

    /**
     * @required
     */
    public function autowireAbstractTypeInferer(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        StaticTypeMapper $staticTypeMapper,
        TypeFactory $typeFactory
    ): void {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeFactory = $typeFactory;
    }
}

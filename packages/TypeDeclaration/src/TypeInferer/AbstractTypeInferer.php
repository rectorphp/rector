<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\StaticTypeMapper;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

abstract class AbstractTypeInferer
{
    /**
     * @var CallableNodeTraverser
     */
    protected $callableNodeTraverser;

    /**
     * @var NameResolver
     */
    protected $nameResolver;

    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @var StaticTypeMapper
     */
    protected $staticTypeMapper;

    /**
     * @required
     */
    public function autowireAbstractTypeInferer(
        CallableNodeTraverser $callableNodeTraverser,
        NameResolver $nameResolver,
        NodeTypeResolver $nodeTypeResolver,
        StaticTypeMapper $staticTypeMapper
    ): void {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nameResolver = $nameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
}

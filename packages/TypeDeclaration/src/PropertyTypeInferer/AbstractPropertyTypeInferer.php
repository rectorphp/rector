<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PropertyTypeInferer;

use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\StaticTypeToStringResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

abstract class AbstractPropertyTypeInferer
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
     * @var StaticTypeToStringResolver
     */
    protected $staticTypeToStringResolver;

    /**
     * @required
     */
    public function autowireAbstractPropertyTypeInferer(
        CallableNodeTraverser $callableNodeTraverser,
        NameResolver $nameResolver,
        NodeTypeResolver $nodeTypeResolver,
        StaticTypeToStringResolver $staticTypeToStringResolver
    ): void {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nameResolver = $nameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeToStringResolver = $staticTypeToStringResolver;
    }
}

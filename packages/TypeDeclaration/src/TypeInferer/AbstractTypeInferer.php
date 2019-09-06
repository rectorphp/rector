<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
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
     * @var TypeFactory
     */
    protected $typeFactory;

    /**
     * @required
     */
    public function autowireAbstractTypeInferer(
        CallableNodeTraverser $callableNodeTraverser,
        NameResolver $nameResolver,
        NodeTypeResolver $nodeTypeResolver,
        StaticTypeMapper $staticTypeMapper,
        TypeFactory $typeFactory
    ): void {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nameResolver = $nameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeFactory = $typeFactory;
    }
}

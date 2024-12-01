<?php

declare (strict_types=1);
namespace Rector\Symfony\DependencyInjection\NodeFactory;

use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\ObjectType;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PostRector\ValueObject\PropertyMetadata;
final class AutowireClassMethodFactory
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param PropertyMetadata[] $propertyMetadatas
     */
    public function create(Trait_ $trait, array $propertyMetadatas) : ClassMethod
    {
        $traitName = $this->nodeNameResolver->getShortName($trait);
        $autowireClassMethod = new ClassMethod('autowire' . $traitName);
        $autowireClassMethod->flags |= Modifiers::PUBLIC;
        $autowireClassMethod->returnType = new Identifier('void');
        $autowireClassMethod->setDocComment(new Doc("/**\n * @required\n */"));
        foreach ($propertyMetadatas as $propertyMetadata) {
            $param = $this->createAutowiredParam($propertyMetadata);
            $autowireClassMethod->params[] = $param;
            $createPropertyAssign = new Assign(new PropertyFetch(new Variable('this'), new Identifier($propertyMetadata->getName())), new Variable($propertyMetadata->getName()));
            $autowireClassMethod->stmts[] = new Expression($createPropertyAssign);
        }
        return $autowireClassMethod;
    }
    private function createAutowiredParam(PropertyMetadata $propertyMetadata) : Param
    {
        $param = new Param(new Variable($propertyMetadata->getName()));
        $objectType = $propertyMetadata->getType();
        if (!$objectType instanceof ObjectType) {
            throw new ShouldNotHappenException();
        }
        $param->type = new FullyQualified($objectType->getClassName());
        return $param;
    }
}

<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PhpDoc;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\Reflection\ReflectionProvider;
use Rector\PhpParser\Node\NodeFactory;
final class PhpDocValueToNodeMapper
{
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(NodeFactory $nodeFactory, ReflectionProvider $reflectionProvider)
    {
        $this->nodeFactory = $nodeFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function mapGenericTagValueNode(GenericTagValueNode $genericTagValueNode) : Expr
    {
        if (\strpos($genericTagValueNode->value, '::') !== \false) {
            [$class, $constant] = \explode('::', $genericTagValueNode->value);
            $name = new Name($class);
            return $this->nodeFactory->createClassConstFetchFromName($name, $constant);
        }
        $reference = \ltrim($genericTagValueNode->value, '\\');
        if ($this->reflectionProvider->hasClass($reference)) {
            return $this->nodeFactory->createClassConstReference($reference);
        }
        return new String_($reference);
    }
}

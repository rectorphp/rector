<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PhpDoc;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\PhpParser\Node\NodeFactory;
final class PhpDocValueToNodeMapper
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
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

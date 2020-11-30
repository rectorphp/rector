<?php
declare(strict_types=1);

namespace Rector\PHPUnit\PhpDoc;

use Nette\Utils\Strings;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\Core\PhpParser\Node\NodeFactory;

final class PhpDocValueToNodeMapper
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function mapGenericTagValueNode(GenericTagValueNode $genericTagValueNode): Expr
    {
        if (Strings::contains($genericTagValueNode->value, '::')) {
            [$class, $constant] = explode('::', $genericTagValueNode->value);
            return $this->nodeFactory->createShortClassConstFetch($class, $constant);
        }

        $reference = ltrim($genericTagValueNode->value, '\\');

        if (class_exists($reference)) {
            return $this->nodeFactory->createClassConstReference($reference);
        }

        return new String_($reference);
    }
}

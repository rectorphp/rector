<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
final class NodeTypes
{
    /**
     * @var array<class-string<PhpDocTagValueNode>>
     */
    public const TYPE_AWARE_NODES = [\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode::class, \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode::class, \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode::class, \PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode::class, \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode::class];
    /**
     * @var string[]
     */
    public const TYPE_AWARE_DOCTRINE_ANNOTATION_CLASSES = ['JMS\\Serializer\\Annotation\\Type', 'Doctrine\\ORM\\Mapping\\OneToMany', 'Symfony\\Component\\Validator\\Constraints\\Choice', 'Symfony\\Component\\Validator\\Constraints\\Email', 'Symfony\\Component\\Validator\\Constraints\\Range'];
}

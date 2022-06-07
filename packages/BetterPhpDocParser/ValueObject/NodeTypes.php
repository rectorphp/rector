<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
final class NodeTypes
{
    /**
     * @var array<class-string<PhpDocTagValueNode>>
     */
    public const TYPE_AWARE_NODES = [VarTagValueNode::class, ParamTagValueNode::class, ReturnTagValueNode::class, ThrowsTagValueNode::class, PropertyTagValueNode::class, TemplateTagValueNode::class];
    /**
     * @var string[]
     */
    public const TYPE_AWARE_DOCTRINE_ANNOTATION_CLASSES = ['RectorPrefix20220607\\JMS\\Serializer\\Annotation\\Type', 'RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\OneToMany', 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Constraints\\Choice', 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Constraints\\Email', 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Constraints\\Range'];
}

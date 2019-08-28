<?php declare(strict_types=1);

namespace Rector\Symfony\PhpDoc\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;

final class RouteTagValueNode implements AttributeAwareNodeInterface, PhpDocTagValueNode
{
    use AttributeTrait;

    /**
     * @var string
     */
    public const SHORT_NAME = '@Route';

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
    }

//    public function __toString(): string
//    {
//        $contentItems = [];
//
//        if ($this->type !== null) {
//            $contentItems['type'] = sprintf('type="%s"', $this->type);
//        }
//
//        if ($this->name !== null) {
//            $contentItems['name'] = sprintf('name="%s"', $this->name);
//        }
//
//        if ($this->length !== null) {
//            $contentItems['length'] = sprintf('length=%s', $this->length);
//        }
//
//        if ($this->precision !== null) {
//            $contentItems['precision'] = sprintf('precision=%s', $this->precision);
//        }
//
//        if ($this->scale !== null) {
//            $contentItems['scale'] = sprintf('scale=%s', $this->scale);
//        }
//
//        if ($this->unique !== null) {
//            $contentItems['unique'] = sprintf('unique=%s', $this->unique ? 'true' : 'false');
//        }
//
//        if ($this->nullable !== null) {
//            $contentItems['nullable'] = sprintf('nullable=%s', $this->nullable ? 'true' : 'false');
//        }
//
//        if ($this->options) {
//            $contentItems['options'] = $this->printArrayItem($this->options, 'options');
//        }
//
//        if ($this->columnDefinition !== null) {
//            $contentItems['columnDefinition'] = sprintf('columnDefinition="%s"', $this->columnDefinition);
//        }
//
//        return $this->printContentItems($contentItems);
//    }

}

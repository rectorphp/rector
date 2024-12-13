<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Class_;

use RectorPrefix202412\Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php80\ValueObject\AttributeValueAndDocComment;
final class AttributeValueResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/CL9ktz/1
     */
    private const END_SLASH_REGEX = '#\\\\\\r?$#';
    public function resolve(AnnotationToAttribute $annotationToAttribute, PhpDocTagNode $phpDocTagNode) : ?AttributeValueAndDocComment
    {
        if (!$annotationToAttribute->getUseValueAsAttributeArgument()) {
            return null;
        }
        $docValue = (string) $phpDocTagNode->value;
        $docComment = '';
        // special case for newline
        if (\strpos($docValue, "\n") !== \false) {
            $keepJoining = \true;
            $docValueLines = \explode("\n", $docValue);
            $joinDocValue = '';
            $hasPreviousEndSlash = \false;
            foreach ($docValueLines as $docValueLine) {
                if ($keepJoining) {
                    $joinDocValue .= \rtrim($docValueLine, '\\\\');
                }
                if (Strings::match($docValueLine, self::END_SLASH_REGEX) === null) {
                    if ($hasPreviousEndSlash === \false) {
                        $docComment = $docValueLine;
                    }
                    $keepJoining = \false;
                } else {
                    $hasPreviousEndSlash = \true;
                }
            }
            $docValue = $joinDocValue;
        }
        return new AttributeValueAndDocComment($docValue, $docComment);
    }
}

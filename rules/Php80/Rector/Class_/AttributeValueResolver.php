<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Class_;

use RectorPrefix202412\Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php80\ValueObject\AttributeValueAndDocComment;
use Rector\Util\NewLineSplitter;
final class AttributeValueResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/CL9ktz/4
     */
    private const END_SLASH_REGEX = '#\\\\$#';
    public function resolve(AnnotationToAttribute $annotationToAttribute, PhpDocTagNode $phpDocTagNode) : ?AttributeValueAndDocComment
    {
        if (!$annotationToAttribute->getUseValueAsAttributeArgument()) {
            return null;
        }
        if ($phpDocTagNode->value instanceof DoctrineAnnotationTagValueNode) {
            $docValue = (string) $phpDocTagNode->value->getOriginalContent();
        } else {
            $docValue = (string) $phpDocTagNode->value;
        }
        $docComment = '';
        // special case for newline
        if (\strpos($docValue, "\n") !== \false) {
            $keepJoining = \true;
            $docValueLines = NewLineSplitter::split($docValue);
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

<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Class_;

use RectorPrefix202506\Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
        $docValue = (string) $phpDocTagNode->value;
        if ($phpDocTagNode->value instanceof DoctrineAnnotationTagValueNode) {
            $originalContent = (string) $phpDocTagNode->value->getOriginalContent();
            if ($docValue === '') {
                $attributeComment = (string) $phpDocTagNode->value->getAttribute(AttributeKey::ATTRIBUTE_COMMENT);
                if ($originalContent === $attributeComment) {
                    $docValue = $originalContent;
                }
            } else {
                $attributeComment = \ltrim($originalContent, $docValue);
                if ($attributeComment !== '') {
                    $docValue .= "\n" . $attributeComment;
                }
            }
        }
        $docComment = '';
        // special case for newline
        if (\strpos($docValue, "\n") !== \false) {
            $keepJoining = \true;
            $docValueLines = NewLineSplitter::split($docValue);
            $joinDocValue = '';
            $hasPreviousEndSlash = \false;
            foreach ($docValueLines as $key => $docValueLine) {
                if ($keepJoining) {
                    $joinDocValue .= \rtrim($docValueLine, '\\\\');
                }
                if (Strings::match($docValueLine, self::END_SLASH_REGEX) === null) {
                    if ($hasPreviousEndSlash === \false && $key > 0) {
                        if ($docComment === '') {
                            $docComment .= $docValueLine;
                        } else {
                            $docComment .= "\n * " . $docValueLine;
                        }
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

<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer;

final class AnnotationFormatRestorer
{
    /**
     * @var ContentPatcher
     */
    private $contentPatcher;

    public function __construct(ContentPatcher $contentPatcher)
    {
        $this->contentPatcher = $contentPatcher;
    }

    public function restore(string $contentOriginal, string $content): string
    {
        $content = $this->contentPatcher->rollbackValidAnnotation(
            $contentOriginal,
            $content,
            ContentPatcher::VALID_ANNOTATION_STRING_REGEX,
            ContentPatcher::INVALID_ANNOTATION_STRING_REGEX
        );
        $content = $this->contentPatcher->rollbackValidAnnotation(
            $contentOriginal,
            $content,
            ContentPatcher::VALID_ANNOTATION_ROUTE_REGEX,
            ContentPatcher::INVALID_ANNOTATION_ROUTE_REGEX
        );
        $content = $this->contentPatcher->rollbackValidAnnotation(
            $contentOriginal,
            $content,
            ContentPatcher::VALID_ANNOTATION_COMMENT_REGEX,
            ContentPatcher::INVALID_ANNOTATION_COMMENT_REGEX
        );
        $content = $this->contentPatcher->rollbackValidAnnotation(
            $contentOriginal,
            $content,
            ContentPatcher::VALID_ANNOTATION_CONSTRAINT_REGEX,
            ContentPatcher::INVALID_ANNOTATION_CONSTRAINT_REGEX
        );
        $content = $this->contentPatcher->rollbackValidAnnotation(
            $contentOriginal,
            $content,
            ContentPatcher::VALID_ANNOTATION_ROUTE_OPTION_REGEX,
            ContentPatcher::INVALID_ANNOTATION_ROUTE_OPTION_REGEX
        );
        $content = $this->contentPatcher->rollbackValidAnnotation(
            $contentOriginal,
            $content,
            ContentPatcher::VALID_ANNOTATION_ROUTE_LOCALIZATION_REGEX,
            ContentPatcher::INVALID_ANNOTATION_ROUTE_LOCALIZATION_REGEX
        );
        $content = $this->contentPatcher->rollbackValidAnnotation(
            $contentOriginal,
            $content,
            ContentPatcher::VALID_ANNOTATION_VAR_RETURN_EXPLICIT_FORMAT_REGEX,
            ContentPatcher::INVALID_ANNOTATION_VAR_RETURN_EXPLICIT_FORMAT_REGEX
        );

        return $this->contentPatcher->rollbackDuplicateComment($contentOriginal, $content);
    }
}

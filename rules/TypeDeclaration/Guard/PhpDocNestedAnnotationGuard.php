<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Guard;

use RectorPrefix202304\Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class PhpDocNestedAnnotationGuard
{
    /**
     * Regex is used to count annotations including nested annotations
     *
     * @see https://regex101.com/r/G7wODT/1
     * @var string
     */
    private const SIMPLE_ANNOTATION_REGEX = '/@[A-z]+\\(?/i';
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * Check if rector accidentally skipped annotation during parsing which it should not have (this bug is likely related to parsing of annotations
     * in phpstan / rector)
     */
    public function isPhpDocCommentCorrectlyParsed(Node $node) : bool
    {
        $comments = $node->getAttribute(AttributeKey::COMMENTS, []);
        if ((\is_array($comments) || $comments instanceof \Countable ? \count($comments) : 0) !== 1) {
            return \true;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        /** @var Doc $phpDoc */
        $phpDoc = $comments[0];
        $originalPhpDocText = $phpDoc->getText();
        /**
         * This is a safeguard to skip cases where the PhpStan / Rector phpdoc parser parses annotations incorrectly (ie.: nested annotations)
         */
        $parsedPhpDocText = (string) $phpDocInfo->getPhpDocNode();
        return !$this->hasAnnotationCountChanged($originalPhpDocText, $parsedPhpDocText);
    }
    private function hasAnnotationCountChanged(string $originalPhpDocText, string $updatedPhpDocText) : bool
    {
        $originalAnnotationCount = \count(Strings::matchAll($originalPhpDocText, self::SIMPLE_ANNOTATION_REGEX));
        $reconstructedAnnotationCount = \count(Strings::matchAll($updatedPhpDocText, self::SIMPLE_ANNOTATION_REGEX));
        return $originalAnnotationCount !== $reconstructedAnnotationCount;
    }
}

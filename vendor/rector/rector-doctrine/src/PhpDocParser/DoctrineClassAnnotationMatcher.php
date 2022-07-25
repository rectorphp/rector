<?php

declare (strict_types=1);
namespace Rector\Doctrine\PhpDocParser;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
class DoctrineClassAnnotationMatcher
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;
    public function __construct(ClassAnnotationMatcher $classAnnotationMatcher)
    {
        $this->classAnnotationMatcher = $classAnnotationMatcher;
    }
    public function resolveExpectingDoctrineFQCN(string $value, Node $node) : ?string
    {
        $fullyQualified = $this->classAnnotationMatcher->resolveTagToKnownFullyQualifiedName($value, $node);
        if ($fullyQualified === null) {
            // Doctrine FQCNs are strange: In their examples
            // they omit the leading slash. This leads to
            // ClassAnnotationMatcher searching in the wrong
            // namespace. Therefor we try to add the leading
            // slash manually here.
            $fullyQualified = $this->classAnnotationMatcher->resolveTagToKnownFullyQualifiedName('\\' . $value, $node);
        }
        return $fullyQualified;
    }
}

<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\TestCase;
/**
 * @implements Rule<InClassNode>
 */
class NoMissingSpaceInClassAnnotationRule implements Rule
{
    /**
     * Covers helper.
     *
     * @var AnnotationHelper
     */
    private $annotationHelper;
    public function __construct(\PHPStan\Rules\PHPUnit\AnnotationHelper $annotationHelper)
    {
        $this->annotationHelper = $annotationHelper;
    }
    public function getNodeType() : string
    {
        return InClassNode::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null || $classReflection->isSubclassOf(TestCase::class) === \false) {
            return [];
        }
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return [];
        }
        return $this->annotationHelper->processDocComment($docComment);
    }
}

<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\FileTypeMapper;
use PHPUnit\Framework\TestCase;
use function array_map;
use function array_merge;
use function array_shift;
use function count;
use function in_array;
use function sprintf;
/**
 * @implements Rule<Node\Stmt\ClassMethod>
 */
class ClassMethodCoversExistsRule implements Rule
{
    /**
     * Covers helper.
     *
     * @var CoversHelper
     */
    private $coversHelper;
    /**
     * The file type mapper.
     *
     * @var FileTypeMapper
     */
    private $fileTypeMapper;
    public function __construct(\PHPStan\Rules\PHPUnit\CoversHelper $coversHelper, FileTypeMapper $fileTypeMapper)
    {
        $this->coversHelper = $coversHelper;
        $this->fileTypeMapper = $fileTypeMapper;
    }
    public function getNodeType() : string
    {
        return Node\Stmt\ClassMethod::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return [];
        }
        if (!$classReflection->isSubclassOf(TestCase::class)) {
            return [];
        }
        $errors = [];
        $classPhpDoc = $classReflection->getResolvedPhpDoc();
        [$classCovers, $classCoversDefaultClasses] = $this->coversHelper->getCoverAnnotations($classPhpDoc);
        $classCoversStrings = array_map(static function (PhpDocTagNode $covers) : string {
            return (string) $covers->value;
        }, $classCovers);
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return [];
        }
        $coversDefaultClass = count($classCoversDefaultClasses) === 1 ? array_shift($classCoversDefaultClasses) : null;
        $methodPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($scope->getFile(), $classReflection->getName(), $scope->isInTrait() ? $scope->getTraitReflection()->getName() : null, $node->name->toString(), $docComment->getText());
        [$methodCovers, $methodCoversDefaultClasses] = $this->coversHelper->getCoverAnnotations($methodPhpDoc);
        $errors = [];
        if (count($methodCoversDefaultClasses) > 0) {
            $errors[] = RuleErrorBuilder::message(sprintf('@coversDefaultClass defined on class method %s.', $node->name))->build();
        }
        foreach ($methodCovers as $covers) {
            if (in_array((string) $covers->value, $classCoversStrings, \true)) {
                $errors[] = RuleErrorBuilder::message(sprintf('Class already @covers %s so the method @covers is redundant.', $covers->value))->build();
            }
            $errors = array_merge($errors, $this->coversHelper->processCovers($node, $covers, $coversDefaultClass));
        }
        return $errors;
    }
}

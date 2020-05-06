<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Type\FileTypeMapper;
use PHPUnit\Framework\TestCase;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\SeeAnnotationToTestRule\SeeAnnotationToTestRuleTest
 */
final class SeeAnnotationToTestRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Class "%s" is missing @see annotation with test case class reference';

    /**
     * @var FileTypeMapper
     */
    private $fileTypeMapper;

    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var class-string[]
     */
    private $requiredSeeTypes = [];

    public function __construct(Broker $broker, FileTypeMapper $fileTypeMapper, array $requiredSeeTypes = [])
    {
        $this->fileTypeMapper = $fileTypeMapper;
        $this->broker = $broker;
        $this->requiredSeeTypes = $requiredSeeTypes;
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $this->matchClassReflection($node);
        if ($classReflection === null) {
            return [];
        }

        if ($this->shouldSkipClassReflection($classReflection)) {
            return [];
        }

        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return [sprintf(self::ERROR_MESSAGE, $classReflection->getName())];
        }

        $resolvedPhpDoc = $this->resolvePhpDoc($scope, $classReflection, $docComment);

        /** @var PhpDocTagNode[] $seeTags */
        $seeTags = $resolvedPhpDoc->getPhpDocNode()->getTagsByName('@see');

        if ($this->containsSeeTestCase($seeTags)) {
            return [];
        }

        return [sprintf(self::ERROR_MESSAGE, $classReflection->getName())];
    }

    private function shouldSkipClassReflection(ClassReflection $classReflection): bool
    {
        if ($classReflection->isAbstract()) {
            return true;
        }

        foreach ($this->requiredSeeTypes as $requiredSeeType) {
            if ($classReflection->isSubclassOf($requiredSeeType)) {
                return false;
            }
        }

        return true;
    }

    private function matchClassReflection(Class_ $node): ?ClassReflection
    {
        if ($node->name === null) {
            return null;
        }

        $className = (string) $node->namespacedName;
        if (! class_exists($className)) {
            return null;
        }

        return $this->broker->getClass($className);
    }

    private function resolvePhpDoc(Scope $scope, ClassReflection $classReflection, Doc $doc): ResolvedPhpDocBlock
    {
        return $this->fileTypeMapper->getResolvedPhpDoc(
            $scope->getFile(),
            $classReflection->getName(),
            null,
            null,
            $doc->getText()
        );
    }

    /**
     * @param PhpDocTagNode[] $seeTags
     */
    private function containsSeeTestCase(array $seeTags): bool
    {
        foreach ($seeTags as $seeTag) {
            if (! $seeTag->value instanceof GenericTagValueNode) {
                continue;
            }

            if (is_a($seeTag->value->value, TestCase::class, true)) {
                return true;
            }
        }

        return false;
    }
}

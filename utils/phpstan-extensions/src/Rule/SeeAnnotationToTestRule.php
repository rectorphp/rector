<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\Rule;
use PHPStan\Type\FileTypeMapper;

final class SeeAnnotationToTestRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Class "%s" is missing @see annotation with test case class reference';

    /**
     * @var string[]
     */
    private const CLASS_SUFFIXES = ['Rector'];

    /**
     * @var FileTypeMapper
     */
    private $fileTypeMapper;

    /**
     * @var Broker
     */
    private $broker;

    public function __construct(Broker $broker, FileTypeMapper $fileTypeMapper)
    {
        $this->fileTypeMapper = $fileTypeMapper;
        $this->broker = $broker;
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
        if ($node->name === null) {
            return [];
        }

        $className = (string) $node->namespacedName;
        if (! $this->isClassMatch($className)) {
            return [];
        }

        $classReflection = $this->broker->getClass($className);

        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return [sprintf(self::ERROR_MESSAGE, $className)];
        }

        $resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
            $scope->getFile(),
            $classReflection->getName(),
            null,
            null,
            $docComment->getText()
        );

        $seeTags = $resolvedPhpDoc->getPhpDocNode()->getTagsByName('@see');
        // @todo validate to refer a TestCase class
        if ($seeTags !== []) {
            return [];
        }

        return [sprintf(self::ERROR_MESSAGE, $className)];
    }

    private function isClassMatch(string $className): bool
    {
        foreach (self::CLASS_SUFFIXES as $classSuffix) {
            if (Strings::endsWith($className, $classSuffix)) {
                return true;
            }
        }

        return false;
    }
}

<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\PHPStan\Rules;

use RectorPrefix20220606\PhpParser\Comment\Doc;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\PHPStan\Rules\Rule;
use RectorPrefix20220606\PHPStan\ShouldNotHappenException;
use RectorPrefix20220606\PHPStan\Type\FileTypeMapper;
use RectorPrefix20220606\Rector\Core\Contract\Rector\PhpRectorInterface;
/**
 * @see \Ssch\TYPO3Rector\PHPStan\Tests\Rules\AddCodeCoverageIgnoreForRectorDefinition\AddCodeCoverageIgnoreForRectorDefinitionTest
 * @implements Rule<ClassMethod>
 */
final class AddCodeCoverageIgnoreForRectorDefinitionRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Provide @codeCoverageIgnore doc block for "%s" RectorDefinition method';
    /**
     * @readonly
     * @var \PHPStan\Type\FileTypeMapper
     */
    private $fileTypeMapper;
    public function __construct(FileTypeMapper $fileTypeMapper)
    {
        $this->fileTypeMapper = $fileTypeMapper;
    }
    public function getNodeType() : string
    {
        return ClassMethod::class;
    }
    /**
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope) : array
    {
        if (!$scope->isInClass()) {
            throw new ShouldNotHappenException();
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return [];
        }
        if (!$classReflection->isSubclassOf(PhpRectorInterface::class)) {
            return [];
        }
        $methodName = $node->name->toString();
        if ('getRuleDefinition' !== $methodName) {
            return [];
        }
        $className = $classReflection->getName();
        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return [\sprintf(self::ERROR_MESSAGE, $className)];
        }
        $resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($scope->getFile(), $classReflection->getName(), null, $methodName, $docComment->getText());
        $phpDocString = $resolvedPhpDoc->getPhpDocString();
        if (\strpos($phpDocString, '@codeCoverageIgnore') !== \false) {
            return [];
        }
        return [\sprintf(self::ERROR_MESSAGE, $className)];
    }
}

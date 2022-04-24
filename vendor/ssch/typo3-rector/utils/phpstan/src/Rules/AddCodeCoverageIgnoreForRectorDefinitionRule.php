<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\PHPStan\Rules;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\FileTypeMapper;
use Rector\Core\Contract\Rector\PhpRectorInterface;
/**
 * @see \Ssch\TYPO3Rector\PHPStan\Tests\Rules\AddCodeCoverageIgnoreForRectorDefinition\AddCodeCoverageIgnoreForRectorDefinitionTest
 * @implements Rule<ClassMethod>
 */
final class AddCodeCoverageIgnoreForRectorDefinitionRule implements \PHPStan\Rules\Rule
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
    public function __construct(\PHPStan\Type\FileTypeMapper $fileTypeMapper)
    {
        $this->fileTypeMapper = $fileTypeMapper;
    }
    public function getNodeType() : string
    {
        return \PhpParser\Node\Stmt\ClassMethod::class;
    }
    /**
     * @return string[]
     */
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        if (!$scope->isInClass()) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return [];
        }
        if (!$classReflection->isSubclassOf(\Rector\Core\Contract\Rector\PhpRectorInterface::class)) {
            return [];
        }
        $methodName = $node->name->toString();
        if ('getRuleDefinition' !== $methodName) {
            return [];
        }
        $className = $classReflection->getName();
        $docComment = $node->getDocComment();
        if (!$docComment instanceof \PhpParser\Comment\Doc) {
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

<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\PHPStan\Rules;

use RectorPrefix20220606\PhpParser\Comment\Doc;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\PHPStan\Rules\Rule;
use RectorPrefix20220606\PHPStan\Type\FileTypeMapper;
use RectorPrefix20220606\Rector\Core\Contract\Rector\PhpRectorInterface;
use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20220606\Ssch\TYPO3Rector\ComposerPackages\Rector\AddPackageVersionRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\General\ConvertImplicitVariablesToExplicitGlobalsRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\General\MethodGetInstanceToMakeInstanceCallRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rules\Rector\Misc\AddCodeCoverageIgnoreToMethodRectorDefinitionRector;
/**
 * @see \Ssch\TYPO3Rector\PHPStan\Tests\Rules\AddChangelogDocBlockForRectorClass\AddChangelogDocBlockForRectorClassTest
 * @implements Rule<Class_>
 */
final class AddChangelogDocBlockForRectorClassRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Provide @changelog doc block for "%s" Rector class';
    /**
     * @var array<class-string<RectorInterface>>
     */
    private const ALLOWED_CLASSES_WITH_NON_CHANGELOG_DOC_BLOCK = [RenameClassMapAliasRector::class, AddCodeCoverageIgnoreToMethodRectorDefinitionRector::class, ConvertImplicitVariablesToExplicitGlobalsRector::class, AbstractTcaRector::class, AddPackageVersionRector::class, MethodGetInstanceToMakeInstanceCallRector::class];
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \PHPStan\Type\FileTypeMapper
     */
    private $fileTypeMapper;
    public function __construct(ReflectionProvider $reflectionProvider, FileTypeMapper $fileTypeMapper)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->fileTypeMapper = $fileTypeMapper;
    }
    public function getNodeType() : string
    {
        return Class_::class;
    }
    /**
     * @param Class_ $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope) : array
    {
        $className = $node->name;
        if (!$className instanceof Identifier) {
            return [];
        }
        $fullyQualifiedClassName = $scope->getNamespace() . '\\' . $className;
        $classReflection = $this->reflectionProvider->getClass($fullyQualifiedClassName);
        if (!$classReflection->isSubclassOf(PhpRectorInterface::class)) {
            return [];
        }
        if (\in_array($fullyQualifiedClassName, self::ALLOWED_CLASSES_WITH_NON_CHANGELOG_DOC_BLOCK, \true)) {
            return [];
        }
        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return [\sprintf(self::ERROR_MESSAGE, $className)];
        }
        $resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($scope->getFile(), $classReflection->getName(), null, null, $docComment->getText());
        $phpDocString = $resolvedPhpDoc->getPhpDocString();
        if (\strpos($phpDocString, '@changelog') !== \false) {
            return [];
        }
        return [\sprintf(self::ERROR_MESSAGE, $className)];
    }
}

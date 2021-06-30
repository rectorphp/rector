<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\PHPStan\Rules;

use RectorPrefix20210630\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\Rule;
use PHPStan\Type\FileTypeMapper;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Ssch\TYPO3Rector\ComposerPackages\Rector\AddPackageVersionRector;
use Ssch\TYPO3Rector\Rector\General\ConvertTypo3ConfVarsRector;
use Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector;
use Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use Ssch\TYPO3Rector\Rules\Rector\Misc\AddCodeCoverageIgnoreToMethodRectorDefinitionRector;
/**
 * @see \Ssch\TYPO3Rector\PHPStan\Tests\Rules\AddChangelogDocBlockForRectorClass\AddChangelogDocBlockForRectorClassTest
 * @implements Rule<Class_>
 */
final class AddChangelogDocBlockForRectorClass implements \PHPStan\Rules\Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Provide @changelog doc block for "%s" Rector class';
    /**
     * @var array<class-string<RectorInterface>>
     */
    private const ALLOWED_CLASSES_WITH_NON_CHANGELOG_DOC_BLOCK = [\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector::class, \Ssch\TYPO3Rector\Rules\Rector\Misc\AddCodeCoverageIgnoreToMethodRectorDefinitionRector::class, \Ssch\TYPO3Rector\Rector\General\ConvertTypo3ConfVarsRector::class, \Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector::class, \Ssch\TYPO3Rector\ComposerPackages\Rector\AddPackageVersionRector::class];
    /**
     * @var \PHPStan\Broker\Broker
     */
    private $broker;
    /**
     * @var \PHPStan\Type\FileTypeMapper
     */
    private $fileTypeMapper;
    public function __construct(\PHPStan\Broker\Broker $broker, \PHPStan\Type\FileTypeMapper $fileTypeMapper)
    {
        $this->broker = $broker;
        $this->fileTypeMapper = $fileTypeMapper;
    }
    public function getNodeType() : string
    {
        return \PhpParser\Node\Stmt\Class_::class;
    }
    /**
     * @param Class_ $node
     * @return string[]
     */
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        $className = $node->name;
        if (null === $className) {
            return [];
        }
        $fullyQualifiedClassName = $scope->getNamespace() . '\\' . $className;
        $classReflection = $this->broker->getClass($fullyQualifiedClassName);
        if (!$classReflection->isSubclassOf(\Rector\Core\Contract\Rector\PhpRectorInterface::class)) {
            return [];
        }
        if (\in_array($fullyQualifiedClassName, self::ALLOWED_CLASSES_WITH_NON_CHANGELOG_DOC_BLOCK, \true)) {
            return [];
        }
        $docComment = $node->getDocComment();
        if (null === $docComment) {
            return [\sprintf(self::ERROR_MESSAGE, $className)];
        }
        $resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($scope->getFile(), $classReflection->getName(), null, null, $docComment->getText());
        $phpDocString = $resolvedPhpDoc->getPhpDocString();
        if (\RectorPrefix20210630\Nette\Utils\Strings::contains($phpDocString, '@changelog')) {
            return [];
        }
        return [\sprintf(self::ERROR_MESSAGE, $className)];
    }
}

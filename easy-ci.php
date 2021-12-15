<?php

declare(strict_types=1);

use PHPStan\PhpDocParser\Parser\TypeParser;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\NodeManipulator\MethodCallManipulator;
use Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer;
use Rector\DependencyInjection\NodeManipulator\PropertyConstructorInjectionManipulator;
use Rector\FileFormatter\Contract\Formatter\FileFormatterInterface;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\Naming\Contract\AssignVariableNameResolverInterface;
use Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory;
use Rector\NodeTypeResolver\Reflection\BetterReflection\RectorBetterReflectionSourceLocatorFactory;
use Rector\Php80\Contract\StrStartWithMatchAndRefactorInterface;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
use Rector\Set\Contract\SetListInterface;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCI\ValueObject\Option;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::TYPES_TO_SKIP, [
        Command::class,
        Application::class,
        RectorInterface::class,
        TypeToCallReflectionResolverInterface::class,
        PropertyTypeInfererInterface::class,
        ParamTypeInfererInterface::class,
        ReturnInterface::class,
        FileProcessorInterface::class,
        ClassNameImportSkipVoterInterface::class,
        StrStartWithMatchAndRefactorInterface::class,
        PhpDocTypeMapperInterface::class,
        PhpParserNodeMapperInterface::class,
        TypeMapperInterface::class,
        AbstractPhpDocNodeVisitor::class,
        NodeNameResolverInterface::class,
        NodeTypeResolverInterface::class,
        ReadNodeAnalyzerInterface::class,
        SetListInterface::class,
        ConflictingNameGuardInterface::class,
        TypeParser::class,
        RectorBetterReflectionSourceLocatorFactory::class,
        AbstractTestCase::class,
        PHPStanServicesFactory::class,
        OutputStyleInterface::class,
        FileFormatterInterface::class,
        MethodCallManipulator::class,
        AssignVariableNameResolverInterface::class,
        // fix later - rector-symfony
        PropertyConstructorInjectionManipulator::class,
        // used in tests
        FileInfoParser::class,
        SameClassMethodCallAnalyzer::class,
        AnnotationToAttributeMapperInterface::class,
    ]);
};

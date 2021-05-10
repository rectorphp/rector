<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use RectorPrefix20210510\PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use RectorPrefix20210510\PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210510\Symplify\EasyCodingStandard\ValueObject\Option;
use RectorPrefix20210510\Symplify\EasyCodingStandard\ValueObject\Set\SetList;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::SYMPLIFY);
    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::CLEAN_CODE);
    $services = $containerConfigurator->services();
    $services->set(GeneralPhpdocAnnotationRemoveFixer::class)->call('configure', [['annotations' => ['throws', 'author', 'package', 'group', 'required', 'phpstan-ignore-line', 'phpstan-ignore-next-line']]]);
    $services->set(NoSuperfluousPhpdocTagsFixer::class)->call('configure', [['allow_mixed' => \true]]);
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/ecs.php', __DIR__ . '/src', __DIR__ . '/tests']);
};

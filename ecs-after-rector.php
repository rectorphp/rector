<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\Comment\NoTrailingWhitespaceInCommentFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NoUnusedImportsFixer::class);
    $services->set(OrderedImportsFixer::class);
    $services->set(NoLeadingImportSlashFixer::class);
    $services->set(NoTrailingWhitespaceInCommentFixer::class);
    $services->set(NoEmptyPhpdocFixer::class);
    $services->set(ClassAttributesSeparationFixer::class);
    $services->set(DeclareStrictTypesFixer::class);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        SetList::DOCTRINE_ANNOTATIONS,
    ]);
};

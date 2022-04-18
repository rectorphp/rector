<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Ssch\TYPO3Rector\Rector\v10\v3\SubstituteResourceFactoryRector;
use Ssch\TYPO3Rector\Rector\v10\v3\UseClassTypo3VersionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v3\UseClassTypo3VersionRector::class);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Linkvalidator\\Repository\\BrokenLinkRepository', 'getNumberOfBrokenLinks', 'isLinkTargetBrokenLink')]);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v3\SubstituteResourceFactoryRector::class);
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure(['TYPO3\\CMS\\Extbase\\Mvc\\Web\\Request' => 'TYPO3\\CMS\\Extbase\\Mvc\\Request', 'TYPO3\\CMS\\Extbase\\Mvc\\Web\\Response' => 'TYPO3\\CMS\\Extbase\\Mvc\\Response']);
};

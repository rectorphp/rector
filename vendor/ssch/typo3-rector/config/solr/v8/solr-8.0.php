<?php

declare (strict_types=1);
namespace RectorPrefix20210601;

use Ssch\TYPO3Rector\Rector\Extensions\solr\v8\SolrConnectionAddDocumentsToWriteServiceAddDocumentsRector;
use Ssch\TYPO3Rector\Rector\Extensions\solr\v8\SolrSiteToSolrRepositoryRector;
use RectorPrefix20210601\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210601\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\Extensions\solr\v8\SolrConnectionAddDocumentsToWriteServiceAddDocumentsRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\Extensions\solr\v8\SolrSiteToSolrRepositoryRector::class);
};

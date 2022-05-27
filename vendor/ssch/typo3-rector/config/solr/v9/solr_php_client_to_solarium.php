<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Ssch\TYPO3Rector\Rector\Extensions\solr\v9\ApacheSolrDocumentToSolariumDocumentRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../../config.php');
    $rectorConfig->rule(ApacheSolrDocumentToSolariumDocumentRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Apache_Solr_Document' => 'ApacheSolrForTypo3\\Solr\\System\\Solr\\Document\\Document', 'Apache_Solr_Response' => 'ApacheSolrForTypo3\\Solr\\System\\Solr\\ResponseAdapter']);
};

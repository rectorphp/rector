<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Ssch\TYPO3Rector\Rector\Extensions\solr\v9\ApacheSolrDocumentToSolariumDocumentRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\Extensions\solr\v9\ApacheSolrDocumentToSolariumDocumentRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Apache_Solr_Document' => 'ApacheSolrForTypo3\\Solr\\System\\Solr\\Document\\Document', 'Apache_Solr_Response' => 'ApacheSolrForTypo3\\Solr\\System\\Solr\\ResponseAdapter']);
};

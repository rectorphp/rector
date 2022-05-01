<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\Extensions\solr\v8\SolrConnectionAddDocumentsToWriteServiceAddDocumentsRector;
use Ssch\TYPO3Rector\Rector\Extensions\solr\v8\SolrSiteToSolrRepositoryRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\Extensions\solr\v8\SolrConnectionAddDocumentsToWriteServiceAddDocumentsRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\Extensions\solr\v8\SolrSiteToSolrRepositoryRector::class);
};

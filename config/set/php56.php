<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use Rector\Config\RectorConfig;
use Rector\Php56\Rector\FuncCall\PowToExpRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(PowToExpRector::class);
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['mcrypt_generic_end' => 'mcrypt_generic_deinit', 'set_socket_blocking' => 'stream_set_blocking', 'ocibindbyname' => 'oci_bind_by_name', 'ocicancel' => 'oci_cancel', 'ocicolumnisnull' => 'oci_field_is_null', 'ocicolumnname' => 'oci_field_name', 'ocicolumnprecision' => 'oci_field_precision', 'ocicolumnscale' => 'oci_field_scale', 'ocicolumnsize' => 'oci_field_size', 'ocicolumntype' => 'oci_field_type', 'ocicolumntyperaw' => 'oci_field_type_raw', 'ocicommit' => 'oci_commit', 'ocidefinebyname' => 'oci_define_by_name', 'ocierror' => 'oci_error', 'ociexecute' => 'oci_execute', 'ocifetch' => 'oci_fetch', 'ocifetchstatement' => 'oci_fetch_all', 'ocifreecursor' => 'oci_free_statement', 'ocifreestatement' => 'oci_free_statement', 'ociinternaldebug' => 'oci_internal_debug', 'ocilogoff' => 'oci_close', 'ocilogon' => 'oci_connect', 'ocinewcollection' => 'oci_new_collection', 'ocinewcursor' => 'oci_new_cursor', 'ocinewdescriptor' => 'oci_new_descriptor', 'ocinlogon' => 'oci_new_connect', 'ocinumcols' => 'oci_num_fields', 'ociparse' => 'oci_parse', 'ociplogon' => 'oci_pconnect', 'ociresult' => 'oci_result', 'ocirollback' => 'oci_rollback', 'ocirowcount' => 'oci_num_rows', 'ociserverversion' => 'oci_server_version', 'ocisetprefetch' => 'oci_set_prefetch', 'ocistatementtype' => 'oci_statement_type']);
};

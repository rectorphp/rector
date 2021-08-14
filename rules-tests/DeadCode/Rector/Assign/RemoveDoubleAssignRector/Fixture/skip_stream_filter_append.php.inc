<?php

$streamFilter = stream_filter_append(STDOUT, 'AFilter');
$streamFilter = stream_filter_append(STDERR, 'AFilter');
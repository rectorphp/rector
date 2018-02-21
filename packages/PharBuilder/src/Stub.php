#!/usr/bin/env php
<?php
Phar::mapPhar('rector.phar');
include 'phar://rector.phar/bin/rector';
__HALT_COMPILER();

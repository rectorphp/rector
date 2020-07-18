<?php

declare(strict_types=1);

use Rector\Celebrity\Rector\BooleanOp\LogicalToBooleanRector;
use Rector\Celebrity\Rector\FuncCall\SetTypeToCastRector;
use Rector\Celebrity\Rector\NotEqual\CommonNotEqualRector;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Renaming\Rector\Function_\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CommonNotEqualRector::class);

    $services->set(RenameFunctionRector::class)
        ->arg('$oldFunctionToNewFunction', [
            'split' => 'explode',
            'join' => 'implode',
            'sizeof' => 'count',
            # https://www.php.net/manual/en/aliases.php
            'chop' => 'rtrim',
            'doubleval' => 'floatval',
            'gzputs' => 'gzwrites',
            'fputs' => 'fwrite',
            'ini_alter' => 'ini_set',
            'is_double' => 'is_float',
            'is_integer' => 'is_int',
            'is_long' => 'is_int',
            'is_real' => 'is_float',
            'is_writeable' => 'is_writable',
            'key_exists' => 'array_key_exists',
            'pos' => 'current',
            'strchr' => 'strstr',
            # mb
            'mbstrcut' => 'mb_strcut',
            'mbstrlen' => 'mb_strlen',
            'mbstrpos' => 'mb_strpos',
            'mbstrrpos' => 'mb_strrpos',
            'mbsubstr' => 'mb_substr',
        ]);

    $services->set(SetTypeToCastRector::class);

    $services->set(LogicalToBooleanRector::class);

    $services->set(VarToPublicPropertyRector::class);
};

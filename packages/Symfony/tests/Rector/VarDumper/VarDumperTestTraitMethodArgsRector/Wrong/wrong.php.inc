<?php declare(strict_types=1);

use Rector\Symfony\Tests\Rector\VarDumper\VarDumperTestTraitMethodArgsRector\Source\ClassWithVarDumperTrait;

$trait = new ClassWithVarDumperTrait();
$trait->assertDumpEquals($dump, $data, 'Some message');
$trait->assertDumpMatchesFormat($dump, $format, 'Some message');

<?php declare(strict_types=1);

$trait = new Symfony\Component\VarDumper\Test\VarDumperTestTrait;
$trait->assertDumpEquals($dump, $data, null, 'Some message');
$trait->assertDumpMatchesFormat($dump, $format, null, 'Some message');

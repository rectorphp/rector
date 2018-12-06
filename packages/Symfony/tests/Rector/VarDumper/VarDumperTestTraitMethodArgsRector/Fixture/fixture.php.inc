<?php

use Rector\Symfony\Tests\Rector\VarDumper\VarDumperTestTraitMethodArgsRector\Source\ClassWithVarDumperTrait;

function varDumperTrait()
{
    $trait = new ClassWithVarDumperTrait();
    $trait->assertDumpEquals($dump, $data, 'Some message');
    $trait->assertDumpMatchesFormat($dump, $format, 'Some message');
}

?>
-----
<?php

use Rector\Symfony\Tests\Rector\VarDumper\VarDumperTestTraitMethodArgsRector\Source\ClassWithVarDumperTrait;

function varDumperTrait()
{
    $trait = new ClassWithVarDumperTrait();
    $trait->assertDumpEquals($dump, $data, null, 'Some message');
    $trait->assertDumpMatchesFormat($dump, $format, null, 'Some message');
}

?>

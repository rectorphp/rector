<?php

use Rector\Symfony4\Tests\Rector\MethodCall\VarDumperTestTraitMethodArgsRector\Source\ClassWithVarDumperTrait;

function varDumperTrait()
{
    $trait = new ClassWithVarDumperTrait();
    $trait->assertDumpEquals($dump, $data, 'Some message');
    $trait->assertDumpMatchesFormat($dump, $data, 'Some message');
}

?>
-----
<?php

use Rector\Symfony4\Tests\Rector\MethodCall\VarDumperTestTraitMethodArgsRector\Source\ClassWithVarDumperTrait;

function varDumperTrait()
{
    $trait = new ClassWithVarDumperTrait();
    $trait->assertDumpEquals($dump, $data, 0, 'Some message');
    $trait->assertDumpMatchesFormat($dump, $data, 0, 'Some message');
}

?>

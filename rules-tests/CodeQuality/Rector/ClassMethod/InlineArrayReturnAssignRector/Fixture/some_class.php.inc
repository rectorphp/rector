<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\Fixture;

function getPerson()
{
    $person = [];
    $person['name'] = 'Timmy';
    $person['surname'] = 'Back';

    return $person;
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\Fixture;

function getPerson()
{
    return ['name' => 'Timmy', 'surname' => 'Back'];
}

?>

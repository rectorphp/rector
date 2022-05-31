<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\Fixture;

final class KeepComments
{
    public function getPerson()
    {
        $person = [];
        // name
        $person['name'] = 'Timmy';
        // surname
        $person['surname'] = 'Back';

        return $person;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\Fixture;

final class KeepComments
{
    public function getPerson()
    {
        return [
            // name
            'name' => 'Timmy',
            // surname
            'surname' => 'Back',
        ];
    }
}

?>

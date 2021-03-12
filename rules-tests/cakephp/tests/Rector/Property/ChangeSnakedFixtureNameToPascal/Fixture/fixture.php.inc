<?php

namespace Rector\CakePHP\Tests\Rector\Property\ChangeSnakedFixtureNameToPascal\Fixture;

class Fixture
{
    protected $fixtures = [
        'app.users',
        'other_app.tags',
        'plugin.posts',
        'plugin.messages/deleted_messages',
    ];
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\Property\ChangeSnakedFixtureNameToPascal\Fixture;

class Fixture
{
    protected $fixtures = [
        'app.Users',
        'other_app.Tags',
        'plugin.Posts',
        'plugin.Messages/DeletedMessages',
    ];
}

?>

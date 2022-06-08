<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\UndefinedStmtIndex\Fixture;

class Fixture
{
    public function fetchIdentities(array $data, array $identities)
    {
        $mappedUsers = [];
        foreach ($identities as $id) {
            $array = $mappedUsers[$id->user_id]->identities;
            $array[] = $id;
            $mappedUsers[$id->user_id]->identities = $array;
        }
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\UndefinedStmtIndex\Fixture;

class Fixture
{
    public function fetchIdentities(array $data, array $identities)
    {
        $mappedUsers = [];
        foreach ($identities as $id) {
            $mappedUsers[$id->user_id]->identities[] = $id;
        }
    }
}

?>

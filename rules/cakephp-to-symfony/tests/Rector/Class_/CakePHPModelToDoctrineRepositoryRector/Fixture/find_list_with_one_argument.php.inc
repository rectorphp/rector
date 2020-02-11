<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class FindListWithOneArgument extends \AppModel
{
    public function getList()
    {
        return $this->find('list', [
            'fields' => ['User.username']
        ]);
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class FindListWithOneArgument
{
}

?>

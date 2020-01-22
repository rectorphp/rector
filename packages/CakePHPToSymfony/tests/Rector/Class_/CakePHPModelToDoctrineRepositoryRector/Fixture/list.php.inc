<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class FindList extends \AppModel
{
    public function getOne()
    {
        return $this->find('list', [
            'conditions' => ['List_.status' => 'pending'],
        ]);

        return $result;
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class FindList
{
}

?>

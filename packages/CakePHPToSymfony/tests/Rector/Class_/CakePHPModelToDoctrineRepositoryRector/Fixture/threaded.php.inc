<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class Phone extends \AppModel
{
    public function getAll()
    {
        return $this->find('threaded', [
            'conditions' => ['article_id' => 50]
        ]);
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class Phone
{
}

?>

<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class FindCount extends \AppModel
{
    public function getAll()
    {
        return $this->find('count', [
            'conditions' => ['article_id' => 50]
        ]);
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class FindCount
{
}

?>

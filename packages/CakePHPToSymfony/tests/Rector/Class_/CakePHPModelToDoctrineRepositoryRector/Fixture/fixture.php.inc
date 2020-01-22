<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class Activity extends \AppModel
{
    public function getAll()
    {
        $result = $this->find('all');

        return $result;
    }

    public function getOne()
    {
        $result = $this->find('first', [
            'conditions' => [
                'Activity.revision_number' => $versionId,
                'Activity.document_id' => $documentId,
            ],
            'order' => [
                'created DESC',
            ],
        ]);

        return $result;
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class Activity
{
}

?>

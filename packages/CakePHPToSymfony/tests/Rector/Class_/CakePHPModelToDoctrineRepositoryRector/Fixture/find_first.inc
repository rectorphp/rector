<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class FindFirst extends \AppModel
{
    public function getOne()
    {
        $result = $this->find('first', [
            'conditions' => [
                'FindFirst.revision_number' => $versionId,
                'FindFirst.document_id' => $documentId,
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

class FindFirst
{
}

?>

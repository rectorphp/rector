<?php

namespace Rector\Core\Tests\Issues\Issue7099\Fixture;

use Rector\Core\Tests\Issues\Issue7099\Source\Document;

class Controller
{
    public function getDocumentAction(int $id): ?Document
    {
        $document = $id === 1 ? new Document() : null;

        if (empty($document)) {
            return null;
        }

        return $document;
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue7099\Fixture;

use Rector\Core\Tests\Issues\Issue7099\Source\Document;

class Controller
{
    public function getDocumentAction(int $id): ?Document
    {
        $document = $id === 1 ? new Document() : null;

        if (!$document instanceof \Rector\Core\Tests\Issues\Issue7099\Source\Document) {
            return null;
        }

        return $document;
    }
}

?>

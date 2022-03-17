<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\SplitMultiAssignRemovePrivate\Fixture;

final class MultiAssignExtract
{
    private $content_type;

    public function run()
    {
        $this->content_type = $content_type = "Something";

        return $content_type;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\SplitMultiAssignRemovePrivate\Fixture;

final class MultiAssignExtract
{
    private $content_type;

    public function run()
    {
        $this->content_type = "Something";
        $content_type = "Something";

        return $content_type;
    }
}

?>

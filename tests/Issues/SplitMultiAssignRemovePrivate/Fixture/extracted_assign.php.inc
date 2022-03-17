<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\SplitMultiAssignRemovePrivate\Fixture;

final class ExtractedAssign
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
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\SplitMultiAssignRemovePrivate\Fixture;

final class ExtractedAssign
{
    public function run()
    {
        $content_type = "Something";

        return $content_type;
    }
}

?>

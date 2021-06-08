<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\FixturePhp80;

use Rector\Core\NodeAnalyzer\ClassAnalyzer;

final class RenameParamDoc
{
    /**
     * @param ClassAnalyzer $analyzer
     */
    public function __construct(
        private ClassAnalyzer $analyzer
    ) {
    }

    public function getAnalyzer(): ClassAnalyzer
    {
        return $this->analyzer;
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\FixturePhp80;

use Rector\Core\NodeAnalyzer\ClassAnalyzer;

final class RenameParamDoc
{
    /**
     * @param ClassAnalyzer $classAnalyzer
     */
    public function __construct(
        private ClassAnalyzer $classAnalyzer
    ) {
    }

    public function getAnalyzer(): ClassAnalyzer
    {
        return $this->classAnalyzer;
    }
}

?>

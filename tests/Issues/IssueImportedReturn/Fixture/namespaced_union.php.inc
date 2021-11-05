<?php

namespace Rector\Core\Tests\Issues\IssueImportedReturn\Fixture;

use Rector\Core\Tests\Issues\IssueImportedReturn\Source;

final class NamespacedUnion
{
    public function run(): Source\Logging|Source\Logging2
    {
        if ($this->config['config']) {

        }

        if (! $this->config['config']) {

        }

        if (! $this->config['config']) {

        }

        if (rand(0, 1)) {
            return new Logging();
        }

        return new Logging2();
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\IssueImportedReturn\Fixture;

use Rector\Core\Tests\Issues\IssueImportedReturn\Source;

final class NamespacedUnion
{
    /**
     * @var string
     */
    private const CONFIG = 'config';
    public function run(): Source\Logging|Source\Logging2
    {
        if ($this->config[self::CONFIG]) {

        }

        if (! $this->config[self::CONFIG]) {

        }

        if (! $this->config[self::CONFIG]) {

        }

        if (rand(0, 1)) {
            return new Logging();
        }

        return new Logging2();
    }
}

?>

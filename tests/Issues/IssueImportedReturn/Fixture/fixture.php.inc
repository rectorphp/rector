<?php

namespace Rector\Core\Tests\Issues\IssueImportedReturn\Fixture;

use Rector\Core\Tests\Issues\IssueImportedReturn\Source\Logging;

final class Fixture
{
    public function run(): Logging
    {
        if ($this->config['config']) {

        }

        if (! $this->config['config']) {

        }

        if (! $this->config['config']) {

        }

        return new Logging();
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\IssueImportedReturn\Fixture;

use Rector\Core\Tests\Issues\IssueImportedReturn\Source\Logging;

final class Fixture
{
    /**
     * @var string
     */
    private const CONFIG = 'config';
    public function run(): Logging
    {
        if ($this->config[self::CONFIG]) {

        }

        if (! $this->config[self::CONFIG]) {

        }

        if (! $this->config[self::CONFIG]) {

        }

        return new Logging();
    }
}

?>

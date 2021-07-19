<?php

namespace Rector\Core\Tests\Issues\IssueImportedReturn\Fixture;

use Rector\Core\Tests\Issues\IssueImportedReturn\Source;

final class Namespaced
{
    public function run(): Source\Logging
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

use Rector\Core\Tests\Issues\IssueImportedReturn\Source;

final class Namespaced
{
    /**
     * @var string
     */
    private const CONFIG = 'config';
    public function run(): Source\Logging
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

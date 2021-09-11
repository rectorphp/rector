<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueChangeAndIfReturnContinueDateTime\Fixture;

class Fixture
{
    public $data = [];

    public function get(array $data, $a, $b, $c, $result): string
    {
        foreach ($data as $value) {
            if ($a || ($b && $c)) {
                continue;
            }

            $this->data[$result] = $value;
        }

        if (! isset($this->data[$result])) {
            return 'a';
        }

        return $this->data[$result];
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueChangeAndIfReturnContinueDateTime\Fixture;

class Fixture
{
    public $data = [];

    public function get(array $data, $a, $b, $c, $result): string
    {
        foreach ($data as $value) {
            if ($a) {
                continue;
            }
            if ($b && $c) {
                continue;
            }
            $this->data[$result] = $value;
        }

        if (! isset($this->data[$result])) {
            return 'a';
        }

        return $this->data[$result];
    }
}

?>

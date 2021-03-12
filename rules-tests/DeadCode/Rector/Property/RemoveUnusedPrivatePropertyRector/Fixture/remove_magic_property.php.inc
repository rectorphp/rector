<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class RemoveMagicProperty
{
    private $unusedProp;

    public function buildMailData(object $recipient)
    {
        $this->mails_to_send[$recipient->email] = "";

        foreach ($this->mails_to_send as $email) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class RemoveMagicProperty
{
    public function buildMailData(object $recipient)
    {
        $this->mails_to_send[$recipient->email] = "";

        foreach ($this->mails_to_send as $email) {
        }
    }
}

?>

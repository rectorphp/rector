<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class StrictSetterTypes
{
    private $email;

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class StrictSetterTypes
{
    private ?string $email = null;

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}

?>

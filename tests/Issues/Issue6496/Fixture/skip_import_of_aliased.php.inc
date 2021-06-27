<?php

namespace Rector\Core\Tests\Issues\Issue6496\Fixture;

use ScrumWorks\OpenApiSchema\Annotation as OA;

final class WalletStaticDataView
{
    /**
     * @OA\Property(description="Map transaction type to label")
     */
    public array $transactionLabelMap;
}

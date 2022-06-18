<?php

namespace Rector\Core\Tests\Issues\DisableAutoImportDocBlock\Fixture;

use ScrumWorks\OpenApiSchema\Annotation as OA;

final class WalletStaticDataView
{
    /**
     * @OA\Property(description="Map transaction type to label")
     */
    public array $transactionLabelMap;
}

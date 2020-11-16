<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue4620\DoNotChangeAnnotationRouteOption\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class InvoiceAdminController.
 */
class InvoiceAdminController
{
    /**
     * @Route("/admin/invoice/show/{id}", options={ "expose" = true, "another_option" = false }, name="invoice_admin_show")
     *
     * @param mixed $id
     */
    public function showAction(Request $request, $id)
    {
        var_dump($request, $id);
    }
}

?>

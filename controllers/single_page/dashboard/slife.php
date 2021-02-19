<?php  
namespace Concrete\Package\Slife\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Slife extends DashboardPageController
{
    public function view()
    {
        $response = new RedirectResponse($this->action('webhooks'));
        $response->send();
    }
}

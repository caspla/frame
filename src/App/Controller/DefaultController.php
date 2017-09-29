<?php
namespace App\Controller;

use Core\Controller\BaseController;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        return $this->render('default/index.phtml');
    }
}
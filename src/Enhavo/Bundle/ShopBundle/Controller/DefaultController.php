<?php

namespace enhavo\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('enhavoShopBundle:Default:index.html.twig', array('name' => $name));
    }
}

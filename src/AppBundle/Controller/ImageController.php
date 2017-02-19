<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ImageController extends Controller
{
    /**
     * @Route("/image/create")
     */
    public function createAction()
    {
        return $this->render('AppBundle:Image:create.html.twig', array(
        ));
    }


}

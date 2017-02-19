<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Image;
use AppBundle\Form\ImageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageController extends Controller
{
    /**
     * @Route("/upload")
     */
    public function uploadAction(Request $request)
    {
        if ($request->isMethod('post')) {
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $imageFile */
            $imageFile = $request->files->get('image');
            $fileName = md5(uniqid()) . '.' . $imageFile->guessExtension();
            $imageFile=$imageFile->move($this->getParameter('images_dir'), $fileName);
            $image = new Image();
            $image->setPath($this->getParameter('public_image_path') . '/' . $fileName);
            $image->setBlobData(file_get_contents($imageFile->getRealPath()));
            $manger = $this->getDoctrine()->getManager();
            $manger->persist($image);
            $manger->flush();
        }
        return $this->render('AppBundle:Image:create.html.twig');
    }

    /**
     * @Route("/")
     */
    public function listAction(Request $request)
    {
        $images = $this->getDoctrine()->getManager()->getRepository('AppBundle:Image')->findAll();
        return $this->render('AppBundle:Image:list.html.twig', array('images' => $images));
    }

    /**
     * @Route("/search")
     */
    public function searchAction()
    {
        return $this->render('AppBundle:Image:list.html.twig', array());
    }

}

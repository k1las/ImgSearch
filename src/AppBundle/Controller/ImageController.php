<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
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
            try {
                /** @var Symfony\Component\HttpFoundation\File\UploadedFile $imageFile */
                $imageFile = $request->files->get('image');
                $fileName = md5(uniqid()) . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_dir'), $fileName);
                $image = new Image();
                $image->setPath($this->getParameter('public_image_path') . '/' . $fileName);
                $manger = $this->getDoctrine()->getManager();
                $manger->persist($image);
                $manger->flush();
                $this->addFlash('success', 'Image was saved!');
            } catch (Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }
        return $this->render('AppBundle:Image:create.html.twig');
    }

    /**
     * @Route("/images/{page}/{limit}")
     */
    public function listAction($page = 1, $limit = 12)
    {
        $images = $this->getDoctrine()->getManager()->getRepository('AppBundle:Image')->getImages($page, $limit);
        $maxPages = ceil($images->count() / $limit);
        $thisPage = $page;
        $images = $images->getIterator();
        return $this->render('AppBundle:Image:list.html.twig', compact('images', 'maxPages', 'thisPage', 'limit'));
    }

    /**
     * @Route("/search/{page}/{limit}")
     */
    public function searchAction(Request $request, $page = 1, $limit = 12)
    {
        if ($request->isMethod('post')) {
            $imagePath = $this->_uploadSearched($request);
            $this->get('session')->set('search_path', $imagePath);
        }
        $images = $this->getDoctrine()->getManager()->getRepository('AppBundle:Image')->searchByImage($page, $limit, $this->get('session')->get('search_path'));
        $maxPages = ceil($images->count() / $limit);
        $thisPage = $page;
        $images = $images->getIterator();
        return $this->render('AppBundle:Image:list.html.twig', compact('images', 'maxPages', 'thisPage', 'limit'));
    }

    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->redirectToRoute('app_image_list');
    }

    /**
     * @Route("/images-search")
     */
    public function imagesSearchAction()
    {
        return $this->render('@App/Image/images-search.html.twig');
    }

    /**Upload Searched Image And Return Path
     * @param Request $request
     * @return string
     */
    private function _uploadSearched(Request $request)
    {
        $imageFile = $request->files->get('image');
        $fileName = md5(uniqid()) . '.' . $imageFile->guessExtension();
        $url = $request->getSchemeAndHttpHost() . '/' . $this->getParameter('searched_images_url') . $fileName;
        $imageFile->move($this->getParameter('searched_images_dir'), $fileName);
        return $url;
    }

}

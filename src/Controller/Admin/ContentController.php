<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Content;
use App\Form\Admin\ContentType;
use App\Repository\Admin\ContentRepository;
use App\Repository\CarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;

/**
 * @Route("/admin/content")
 */
class ContentController extends AbstractController
{
    /**
     * @Route("/", name="admin_content_index", methods={"GET"})
     */
    public function index(ContentRepository $contentRepository): Response
    {
        return $this->render('admin/content/index.html.twig', [
            'contents' => $contentRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="admin_content_new", methods={"GET","POST"})
     */
    public function new(Request $request,$id, CarRepository $carRepository,ContentRepository $contentRepository): Response
    {
        $contents=$contentRepository->findBy(['carid'=>$id]);

        $car=$carRepository->findOneBy(['id'=>$id]);
        //echo $car->getTitle();
        //dump($car);
        //die();
        $content = new Content();
        $form = $this->createForm(ContentType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //*****************file upload******************>>>>>>>>>>>>

            /**@var file $file */
            $file = $form['image']->getData();
            if($file){
                $fileName = $this->generateUniqueFileName() . '.' .$file->guessExtension();  // uniq id ile kaydediyor yüklenen resimleri dosya yolu ile beraber alıyor
                //Move the file to the directory where brochures are stored
                try{
                    $file->move(
                        $this->getParameter('images_directory'), //Servis.yaml de tanımladığımız resim yolu
                        $fileName
                    );
                } catch (FileException $e){
                    //... handle exception if something happens during file upload
                }
                $content->setImage($fileName);  //Related upload file name with Car table image field
            }
            //*****************file upload******************>>>>>>>>>>>>

            $entityManager = $this->getDoctrine()->getManager();
            $content->setCarid($car->getId());
            //$content->setCarid($id);
            $entityManager->persist($content);
            $entityManager->flush();

            return $this->redirectToRoute('admin_content_new', ['id' => $id]);
        }

        return $this->render('admin/content/new.html.twig', [
            'car' => $car,
            'content' => $content,
            'contents' => $contents,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @return string
     */

    private function generateUniqueFileName()
    {
        //md5() reduces the similarity of the file names generated by
        //uniqid(), which is based on timestamps
        return md5(uniqid());
    }



    /**
     * @Route("/{id}", name="admin_content_show", methods={"GET"})
     */
    public function show(Content $content): Response
    {
        return $this->render('admin/content/show.html.twig', [
            'content' => $content,
        ]);
    }

    /**
     * @Route("/{id}/edit/{cid}", name="admin_content_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, $cid, Content $content): Response
    {
        $form = $this->createForm(ContentType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            //*****************file upload******************>>>>>>>>>>>>

            /**@var file $file */
            $file = $form['image']->getData();
            if($file){
                $fileName = $this->generateUniqueFileName() . '.' .$file->guessExtension();  // uniq id ile kaydediyor yüklenen resimleri dosya yolu ile beraber alıyor
                //Move the file to the directory where brochures are stored
                try{
                    $file->move(
                        $this->getParameter('images_directory'), //Servis.yaml de tanımladığımız resim yolu
                        $fileName
                    );
                } catch (FileException $e){
                    //... handle exception if something happens during file upload
                }
                $content->setImage($fileName);  //Related upload file name with Car table image field
            }
            //*****************file upload******************>>>>>>>>>>>>


            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_content_new', ['id' => $cid]);
        }

        return $this->render('admin/content/edit.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/{cid}", name="admin_content_delete", methods={"DELETE"})
     */
    public function delete(Request $request,$cid, Content $content): Response
    {
        if ($this->isCsrfTokenValid('delete'.$content->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($content);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_content_new', ['id' => $cid]);
    }
}
<?php

namespace App\Controller;

use App\Controller\Admin\CommentController;
use App\Entity\Admin\Messages;
use App\Entity\Car;
use App\Entity\Setting;
use App\Form\Admin\MessagesType;
use App\Repository\Admin\CommentRepository;
use App\Repository\Admin\ContentRepository;
use App\Repository\CarRepository;
use App\Repository\ImageRepository;
use App\Repository\SettingRepository;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Bridge\Google\Smtp\GmailTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SettingRepository $settingRepository,CarRepository $carRepository)
    {
        $setting = $settingRepository->findAll();
        $slider = $carRepository->findBy(['status'=>'True'],['title'=>'ASC'],3);
        $cars = $carRepository->findBy(['status'=>'True'],['title'=>'DESC'],6);
        $newcars = $carRepository->findBy(['status'=>'True'],['title'=>'DESC'],5); // ilk parametreye adminin onayladığı verileri getirmek için status true demeliyiz.

        //array findBy(array $criteria, array $orderBy = null, int|null $limit = null, int|null $offset = null)
        //dump($slider);  //gelenlerin kontrolünü göstermek için kullandık.
        //die();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'setting' => $setting,
            'slider' => $slider,
            'cars' => $cars,
            'newcars' => $newcars,

        ]);
    }

    /**
     * @Route("/car/{id}", name="car_show", methods={"GET"})
     */
    public function show(Car $car, $id, ImageRepository $imageRepository,CommentRepository $commentRepository, ContentRepository $contentRepository): Response
    {
        $images = $imageRepository->findBy(['car'=>$id]);
        $comments = $commentRepository->findBy(['carid'=>$id, 'status'=>'True']);// sadece true olanlar gelsin dedik burada.
        $contents = $contentRepository->findBy(['carid'=>$id, 'status'=>'True']); //Adminin true dediği resimleri göndereceğiz.

        return $this->render('home/carshow.html.twig', [
            'car' => $car,
            'images' => $images,
            'comments' => $comments,
            'contents' => $contents,

        ]);
    }


    /**
     * @Route("/about", name="home_about")
     */
    public function about(SettingRepository $settingRepository): Response
    {
        $setting = $settingRepository->findAll();
        return $this->render('home/aboutus.html.twig', [
            'setting' => $setting,

        ]);
    }


    /**
     * @Route("/contact", name="home_contact", methods={"GET","POST"})
     */
    public function contact(SettingRepository $settingRepository,Request $request): Response
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token');

        $setting = $settingRepository->findAll();  //Get setting data

        //dump($request);
        //die();

        if ($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('form-message', $submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();
                $message->setStatus('New');
                $message->setIp($_SERVER['REMOTE_ADDR']);
                $entityManager->persist($message);
                $entityManager->flush();
                $this->addFlash('success','Your message has been sent successfuly.Thank you !');

                //----------------------SEND EMAIL------------------------>>>>>>>>>>>>
                $email = (new Email())
                    ->from($setting[0]->getSmtpemail())   //karabuktest@gmail adlı mailden gelecek
                    ->to($form['email']->getData())       //Contact taki formu dolduran kişiye gitmeli.
                    //->cc('cc@example.com')
                    //->bcc('bcc@example.com')
                    //->replyTo('fabien@example.com')
                    //->priority(Email::PRIORITY_HIGH)

                    ->subject('AllCar Your Request')
                    //->text('Simple Text')

                    ->html("Dear ".$form['name']->getData() ."<br>
                                 <p>We will evaluate your requests and contact you as soon as possible</p>
                                 Thank You for your message <br>
                                 =================================================
                                 <br>".$setting[0]->getCompany()."  <br>
                                 Address  : ".$setting[0]->getAddress()."<br>
                                 Phone    : ".$setting[0]->getPhone()."<br>"
                    );

                //smtpemail kullanıcı adı user vs..
                $transport = new GmailTransport($setting[0]->getSmtpemail(), $setting[0]->getSmtppassword());
                $mailer = new Mailer($transport);
                $mailer->send($email);

                //<<<<<<<<<<<<<<<<<<<<<<<<<<-----------SEND EMAIL----------------->>>>>>>>>>>

                return $this->redirectToRoute('home_contact');
            }
        }

        return $this->render('home/contact.html.twig', [
            'setting' => $setting,
            'form' => $form->createView(),


        ]);
    }




}

<?php

namespace App\Controller;

use App\Entity\Admin\Comment;
use App\Entity\Admin\Reservation;
use App\Entity\User;
use App\Form\Admin\CommentType;
use App\Form\Admin\ReservationType;
use App\Form\UserType;
use App\Repository\Admin\CommentRepository;
use App\Repository\Admin\ContentRepository;
use App\Repository\Admin\ReservationRepository;
use App\Repository\CarRepository;
use App\Repository\UserRepository;
use Cassandra\Date;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('user/show.html.twig');
    }

    /**
     * @Route("/comments", name="user_comments", methods={"GET"})
     */
    public function comments( CommentRepository $commentRepository): Response
    {
        $user = $this->getUser();
        //echo $user->getId();
        //die();
        $comments=$commentRepository->getAllCommentsUser($user->getId());
        //dump($comments);
        //die();
        return $this->render('user/comments.html.twig', [
            'comments'=>$comments,
        ] );
    }


    /**
     * @Route("/cars", name="user_cars", methods={"GET"})
     */
    public function cars(CarRepository $carRepository): Response
    {
        $user = $this->getUser();  //Get login user data

        return $this->render('user/cars.html.twig',[
            'cars' => $carRepository->findBy(['userid'=>$user->getId()]),
        ]);
    }



    /**
     * @Route("/reservations", name="user_reservations", methods={"GET"})
     */
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();  //Get login user data
        //$reservations=$reservationRepository->findBy(['userid'=>$user->getId()]);
        $reservations=$reservationRepository->getUserReservation($user->getId());
        //dump($reservations);
        //die();
        return $this->render('user/reservations.html.twig',[
            'reservations' => $reservations,
        ]);
    }


    /**
     * @Route("/reservation/{id}", name="user_reservation_show", methods={"GET"})
     */
    public function reservationshow($id, ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();  //Get login user data
        //$reservations=$reservationRepository->findBy(['userid'=>$user->getId()]);
        $reservation=$reservationRepository->getReservation($id); //id sini gönderdiğim reservation ı getir demeliyiz.
        //dump($reservations);
        //die();
        return $this->render('user/reservation_show.html.twig',[
            'reservation' => $reservation,  //bir tane olduğu için bir tane gönderdik.
        ]);
    }



    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

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
                $user->setImage($fileName);  //Related upload file name with Car table image field
            }
            //*****************file upload******************>>>>>>>>>>>>

            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, $id, User $user, UserPasswordEncoderInterface $passwordEncoder): Response
    {

        $user = $this->getUser(); //Get login User data
        if ($user->getId() != $id)
        {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(UserType::class, $user);
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
                $user->setImage($fileName);  //Related upload file name with Car table image field
            }
            //*****************file upload******************>>>>>>>>>>>>

            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
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
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }


    /**
     * @Route("/newcomment/{id}", name="user_new_comment", methods={"GET","POST"})
     */
    public function newcomment(Request $request, $id): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('comment', $submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();

                $comment->setStatus('New');
                $comment->setIp($_SERVER['REMOTE_ADDR']);
                $comment->setCarid($id);
                $user = $this->getUser();
                $comment->setUserid($user->getId());

                $entityManager->persist($comment);
                $entityManager->flush();
                $this->addFlash('success','Your comment has been sent successfuly.Thank you !');

                return $this->redirectToRoute('car_show', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('car_show', ['id' => $id]);

    }



    /**
     * @Route("/reservation/{cid}/{rid}", name="user_reservation_new", methods={"GET","POST"})
     */
    public function newreservation(Request $request,$cid,$rid, CarRepository $carRepository, ContentRepository $contentRepository): Response
    {
        $car = $carRepository->findOneBy(['id'=>$cid]);
        $content = $contentRepository->findOneBy(['id'=>$rid]);

        $days=$_REQUEST["days"];
        $pickup=$_REQUEST["pickup"];
        $dropoff= Date("Y-m-d H:i:s", strtotime($pickup ." $days Day")); //Adding days to date
        $pickup= Date("Y-m-d H:i:s", strtotime($pickup ." 0 Day"));

        $data["total"]=$days * $content->getPrice();
        $data["days"]=$days;
        $data["pickup"]=$pickup;
        $data["dropoff"]=$dropoff;

        $total = $days* $content->getPrice();
        //echo $total;
        //die();

        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('form-reservation', $submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();

                $pickup=date_create_from_format("Y-m-d H:i:s",$pickup);
                $dropoff=date_create_from_format("Y-m-d H:i:s",$dropoff);


                $reservation->setPickup($pickup);
                $reservation->setDropoff($dropoff);
                $reservation->setStatus('New');
                $reservation->setIp($_SERVER['REMOTE_ADDR']);
                $reservation->setCarid($cid);
                $reservation->setContentid($rid);
                $user = $this->getUser();   //Get login User data
                $reservation->setUserid($user->getId());
                $reservation->setDays($days);
                $reservation->setTotal($data["total"]);
                $reservation->setCreatedAt(new \DateTime()); //Get now datatime

                $entityManager->persist($reservation);
                $entityManager->flush();

                return $this->redirectToRoute('user_reservations');
            }
        }

        return $this->render('user/newreservation.html.twig', [
            'reservation' => $reservation,
            'content' => $content,
            'car' => $car,
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }



}

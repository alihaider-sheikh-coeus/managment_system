<?php

namespace App\Controller;

use App\Repository\ReviewRepository;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use App\Service\AutherizedApiAccess;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    private $reviewRepository;
    private $shopRepository;
    private $userRepository;
    private $authorizedApiAccess;
    public function __construct(ReviewRepository $reviewRepository,ShopRepository $shopRepository,UserRepository $userRepository,AutherizedApiAccess $autherizedApiAccess)
    {
        $this->reviewRepository=$reviewRepository;
        $this->shopRepository=$shopRepository;
        $this->userRepository=$userRepository;
        $this->authorizedApiAccess=$autherizedApiAccess;
    }

    /**
     * @Route("/reviews", name="review",methods={"GET"})
     */
    public function index(): Response
    {
        $reviews = $this->reviewRepository->findAll();

        return $this->render('review/index.html.twig', [
            "reviews"=>$reviews
        ]);
    }
    /**
     * @Route("/status_update/{id}/{status}", name="status_update",methods={"GET"})
     */
    public function statusUpdate($id,$status)
    {
       $this->reviewRepository->updateStatus($id,$status);
       return $this->redirectToRoute('review');
    }

    /**
     * @Route("/api/review/add", name="add_review",methods={"POST"})
     */
    public function add(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);


        $userStatus = $this->userRepository->validateUserId($data['user_id']);

        $shopStatus=$this->shopRepository->validateShopId($data['shop_id']);
        $check=$this->authorizedApiAccess->authorize($request);
        if(!$check)
        {
            $data=["message"=>"user is unauthorized"];
            $response= new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }
       elseif(empty($userStatus))
        {
            $data=["message"=>"user is not found!"];
            $response= new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }
        elseif (empty($shopStatus))
        {
            $data=["message"=>"shop is not found!"];
            $response= new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }
        else
        {
            $content = $data['content'];
            $status = $data['status'];
            if (empty($content) || empty($status) ) {
                throw new NotFoundHttpException('Expecting mandatory parameters!');
            }
            $this->reviewRepository->saveReview($content, $status,$shopStatus,$userStatus);
           $response= new JsonResponse(['status' => 'Review created!'], Response::HTTP_CREATED);
        }
        return $response;
  }

}

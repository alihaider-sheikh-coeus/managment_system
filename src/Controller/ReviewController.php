<?php

namespace App\Controller;

use App\Entity\Review;
use App\EventListener\apiCallEvent;
use App\Repository\ReviewRepository;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use App\Service\AutherizedApiAccess;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use phpDocumentor\Reflection\Types\Float_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReviewController extends AbstractController
{
    private $reviewRepository;
    private $shopRepository;
    private $userRepository;
    private $authorizedApiAccess;
    private $manager;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(ReviewRepository $reviewRepository,EntityManagerInterface $manager,ShopRepository $shopRepository,UserRepository $userRepository,AutherizedApiAccess $autherizedApiAccess,ValidatorInterface $validator,EventDispatcherInterface $eventDispatcher)
    {
        $this->reviewRepository=$reviewRepository;
        $this->shopRepository=$shopRepository;
        $this->userRepository=$userRepository;
        $this->authorizedApiAccess=$autherizedApiAccess;
        $this->manager=$manager;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }
    const ITEMS_PER_PAGE = 4;
    /**
     * @Route("/reviews", name="review",methods={"GET"})
     */
    public function index(Request $request,PaginationService $pagination):Response
    {
        $query   = $this->getDoctrine()->getManager()->getRepository(Review::class)->createQueryBuilder('p');

        $results = $pagination->paginate($query, $request, self::ITEMS_PER_PAGE);

        return $this->render('review/index.html.twig', [
            'reviews' => $results,
            'lastPage' => $pagination->lastPage($results)
        ]);
  }
    /**
     * @Route("/status_update/{id}/{status}", name="status_update",methods={"GET"})
     */
    public function statusUpdate(Request $request,$id,$status)
    {
       $this->reviewRepository->updateStatus($id,$status);

        $referer = $request->headers->get('referer');

       ($status === "approve")? $this->addFlash('success', 'Review has been approved.'):$this->addFlash('error', 'Review has been rejected.');
        return $this->redirect($referer);
    }

    /**
     * @Route("/api/review/add", name="add_review",methods={"POST"})
     */
    public function add(Request $request): Response
    {
        $check=$this->authorizedApiAccess->authorize($request);
        $response=array();
        if(!$check)
        {
            $data=["message"=>"user is unauthorized"];
            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        $userStatus = $this->userRepository->validateUserId($data['user_id']);
        $shopStatus=$this->shopRepository->validateShopId($data['shop_id']);
       if(!$userStatus)
        {
            $data=["message"=>"user is not found!"];
            $response= new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }
        elseif (!$shopStatus)
        {
            $data=["message"=>"shop is not found!"];
            $response= new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }
        else
        {
            $review= new Review();
            $review->setContent($data['content']);
            $review->setStatus($data['status']);
            $errors=$this->validator->validate($review);
            if (count($errors) > 0) {
                throw new \Exception($errors[0]->getMessage());

            } else {
               $this->reviewRepository->saveReview($review,$data,$shopStatus,$userStatus);
                $response= new JsonResponse(['status' => 'Review created!'], Response::HTTP_CREATED);
            }
        }
        return $response;
  }
}

<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use App\Service\AutherizedApiAccess;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use phpDocumentor\Reflection\Types\Float_;
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
    private $manager;
    public function __construct(ReviewRepository $reviewRepository,EntityManagerInterface $manager,ShopRepository $shopRepository,UserRepository $userRepository,AutherizedApiAccess $autherizedApiAccess)
    {
        $this->reviewRepository=$reviewRepository;
        $this->shopRepository=$shopRepository;
        $this->userRepository=$userRepository;
        $this->authorizedApiAccess=$autherizedApiAccess;
        $this->manager=$manager;
    }
    const ITEMS_PER_PAGE = 2;
    /**
     * @Route("/reviews", name="review",methods={"GET"})
     */
    public function index(Request $request,PaginationService $pagination):Response
    {
        $query   = $this->getDoctrine()->getManager()->getRepository(Review::class)->createQueryBuilder('d');
        $results = $pagination->paginate($query, $request, self::ITEMS_PER_PAGE);
//        dd($results);
        return $this->render('review/index.html.twig', [
            'reviews' => $results,
            'lastPage' => $pagination->lastPage($results)
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
    /**
     * Paginator Helper
     *
     * Pass through a query object, current page & limit
     * the offset is calculated from the page and limit
     * returns an `Paginator` instance, which you can call the following on:
     *
     *     $paginator->getIterator()->count() # Total fetched (ie: `5` posts)
     *     $paginator->count() # Count of ALL posts (ie: `20` posts)
     *     $paginator->getIterator() # ArrayIterator
     *
     * @param Doctrine\ORM\Query $dql   DQL Query Object
     * @param integer            $page  Current page (defaults to 1)
     * @param integer            $limit The total number per page (defaults to 5)
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function paginate($dql, $page, $limit)
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }

}

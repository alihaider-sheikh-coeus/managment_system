<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    private $manager,$queryBuilder;

    public function __construct(ManagerRegistry $registry,EntityManagerInterface $manager)
    {
        parent::__construct($registry, Review::class);
        $this->manager = $manager;
    }
    public function saveReview($content, $status, $shop_id,$user_id)
    {
        $newReview = new Review();

        $newReview
            ->setContent($content)
            ->setStatus($status)
            ->setShopId($shop_id)
            ->setUserId($user_id);

        $this->manager->persist($newReview);
        $this->manager->flush();
    }
public function getEmployeeRecord($id)
{
    $qb = $this->manager->createQueryBuilder();

    $qb->select('review')
        ->from('App:Review', 'review')
        ->where('review.id = :reviewId')
        ->setParameter('reviewId',$id);

    return $qb->getQuery()->getResult();
}
    public function updateStatus($id,$status)
    {
        $record = $this->getEmployeeRecord($id);
        $record[0]->setStatus($status);
        $this->manager->persist($record[0]);
        $this->manager->flush();
    }

}

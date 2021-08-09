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
    public function saveReview($review,$data,$shop,$User)
    {
       $review
            ->setContent($data['content'])
            ->setStatus($data['status'])
            ->setShopId($shop)
            ->setUserId($User);

        $this->manager->persist($review);
        $this->manager->flush();
    }
    public function updateStatus($id,$status)
    {
        $record = $this->find($id);
        $record->setStatus($status);
        $this->manager->persist($record);
        $this->manager->flush();
    }

}

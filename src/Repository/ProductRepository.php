<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getFilter($filter){
        $query = $this->createQueryBuilder('p');

        $query->leftJoin('App\Entity\Category', 'c', 'WITH', 'p.category=c.id')
            ->andWhere('p.status = true')
            ->andWhere('c.active = true')
            ->orderBy('p.code', 'ASC');
        if (isset($filter['name']) && $filter['name'] !== ""){
            $query->andWhere('p.name LIKE :name');
            $query->setParameter('name', '%'.trim($filter['name']).'%');
        }
        if (isset($filter['code']) && $filter['code'] !== ""){
            $query->andWhere('p.code LIKE :code');
            $query->setParameter('code', '%'.trim($filter['code']).'%');
        }
        if (isset($filter['category'])){
            $query->andWhere('p.category = :category');
            $query->setParameter('category', $filter['category']);
        }

        return $query;
    }



    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

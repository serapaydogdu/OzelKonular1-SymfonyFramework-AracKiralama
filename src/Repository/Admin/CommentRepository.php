<?php

namespace App\Repository\Admin;

use App\Entity\Admin\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Array_;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // **** ***** LEFT JOIN WITH SQL ***************************

    public function getAllComments():array
    {
        $conn =$this->getEntityManager()->getConnection();

        $sql = '
           SELECT C.*,u.name,u.surname,h.title FROM comment c
           JOIN user u ON u.id = c.userid
           JOIN car h ON h.id = c.carid
           ORDER BY c.id DESC 
        ';

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        //returns an array of arrays (i.e raw data set)
        return $stmt->fetchAll();
    }


    // *****************LEFT JOIN WITH DOCTRINE************************************

    public function getAllCommentsUser($userid): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.id','c.subject','c.comment','c.rate','c.created_at','c.carid','c.status','h.title')
            ->leftJoin('App\Entity\Car','h','WITH','h.id = c.carid')
            ->where('c.userid = :userid')
            ->setParameter('userid', $userid)
            ->orderBy('c.id','DESC');

        $query = $qb->getQuery();
        return $query->execute();
    }

}

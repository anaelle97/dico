<?php

namespace App\Repository;

use App\Entity\BucketList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BucketList|null find($id, $lockMode = null, $lockVersion = null)
 * @method BucketList|null findOneBy(array $criteria, array $orderBy = null)
 * @method BucketList[]    findAll()
 * @method BucketList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BucketListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BucketList::class);
    }

    public function findWishList(int $page = 1): ?array {

        //en QueryBuilder
        $queryBuilder = $this->createQueryBuilder('w');

        //ajoute des clauses where
        $queryBuilder
            ->andWhere('w.isPublished = true');

        //ajoute une jointure à notre requête pour éviter les multiples requêtes SQL
        $queryBuilder->leftJoin('w.category', 'c')
            ->addSelect('c');

        //on peut ajouter des morceaux de requête en fonction de variable php par exemple \o/
        $filterLikes = true;
        if ($filterLikes){
            $queryBuilder->andWhere('w.likes > :likesCount');
            $queryBuilder->setParameter(':likesCount', 300);
        }

        //modifie le qb pour sélectionner le count plutôt !
        //ici on souhaite sélectionner d'abord le nombre de résultats que la requête nous aurait
        //retourné si nous n'avions pas limité le nombre !
        $queryBuilder->select("COUNT(w)");

        //on l'exécute en récupérant que le chiffre du résultat
        $countQuery = $queryBuilder->getQuery();
        $totalResultCount = $countQuery->getSingleScalarResult();


        //mainteant, on peut récupérer les résultats qui nous intéressent en modifiant
        //le même querybuilder !


        //maintenant on veut sélectionner les entités
        $queryBuilder->select("w");

        //notre offset
        //combien de résultats est-ce qu'on évite de récupérer
        //page1 : offset = 0
        //page2 : offset = 20
        //page3 : offset = 40
        $offset = ($page - 1) * 20;
        $queryBuilder->setFirstResult($offset);

        //nombre max de résultats
        $queryBuilder->setMaxResults(20);

        //le tri
        $queryBuilder->addOrderBy('w.dateCreated', 'DESC');

        //on récupère l'objet Query de doctrine
        $query = $queryBuilder->getQuery();

        $paginator = new Paginator($query);

        //on exécute la requête et on récupère les résultats
        $result = $query->getResult();

        //puisqu'on a 2 données à return de cette fonction, on les return dans un tableau
        return [
            "result" => $paginator,
            "totalResultCount" => $totalResultCount,
        ];

        //en DQL
    /*    $dql = "SELECT w
                FROM App\Entity\BucketList w
                WHERE w.isPublished = true
                AND w.likes > 300
                ORDER BY w.dateCreated DESC ";

        //on récupère l'entity manager
        $entityManager = $this->getEntityManager();
        //on crée la requête Doctrine
        $query = $entityManager->createQuery($dql);

        //limite le nombre de résultats
        $query->setMaxResults(20);

        $offset = ($page-1)*20;
        $query->setFirstResult($offset);

        // on execute la requete et on recupere les résultats.
        $result = $query->getResult();

        return $result; */

    }

    // /**
    //  * @return BucketList[] Returns an array of BucketList objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BucketList
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

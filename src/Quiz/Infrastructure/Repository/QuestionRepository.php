<?php

namespace App\Quiz\Infrastructure\Repository;

use App\Quiz\Domain\Entity\Category;
use App\Quiz\Domain\Entity\Level;
use App\Quiz\Domain\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;

/**
 * @extends ServiceEntityRepository<Question>
 *
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function save(Question $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Question $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findRandomByCategoryAndLevel(Category $category, Level $level, int $limit = 10): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT q.id
        FROM question q
        WHERE q.category_id = :category
        AND q.level_id = :level
        ORDER BY RAND()
        LIMIT :limit
    ';

        $stmt = $conn->prepare($sql);

        $stmt->bindValue('category', $category->getId(), \PDO::PARAM_LOB);
        $stmt->bindValue('level', $level->getId(), \PDO::PARAM_LOB);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);

        $questionIds = $stmt->executeQuery()->fetchFirstColumn();

        if (empty($questionIds)) {
            return [];
        }

        return $this->createQueryBuilder('q')
            ->where('q.id IN (:ids)')
            ->setParameter('ids', $questionIds)
            ->getQuery()
            ->getResult();
    }
//    /**
//     * @return Question[] Returns an array of Question objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('q.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Question
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

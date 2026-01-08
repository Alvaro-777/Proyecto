<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Usuario>
 */
class UsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    /**
     * @return boolean Returns true if the email exists in the table
     */
    public function existsByCorreo(string $correo): bool
    {
        $result = $this->createQueryBuilder('u')
            ->andWhere('u.correo = :correo')
            ->setParameter('correo', $correo)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;

        return count($result) > 0;
    }

    public function findOneByCorreo($correo): ?Usuario
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.correo = :val')
            ->setParameter('val', $correo)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

//    /**
//     * @return Usuario[] Returns an array of Usuario objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Usuario
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

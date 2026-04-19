<?php
// src/Repository/Parametres2faRepository.php

namespace App\Repository;

use App\Entity\Parametres2fa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Parametres2faRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parametres2fa::class);
    }
}
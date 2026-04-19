<?php

namespace App\Service;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private NotificationRepository $repo
    ) {}

    public function createOnce(
    int $projetId,
    ?int $capteurId,
    string $type,
    string $severity,
    string $message
): void {

    $n = new Notification();
    $n->setProjetId($projetId)
      ->setCapteurId($capteurId)
      ->setType($type)
      ->setSeverity($severity)
      ->setMessage($message);

    $this->em->persist($n);
}
    
}
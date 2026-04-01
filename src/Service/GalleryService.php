<?php

namespace App\Service;

use App\Entity\Gallery;
use App\Repository\GalleryRepository;
use Doctrine\ORM\EntityManagerInterface;

class GalleryService
{
    public function __construct(
        private GalleryRepository $galleryRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Get all active gallery items
     */
    public function getActiveGallery(): array
    {
        return $this->galleryRepository->findActive();
    }

    /**
     * Get gallery items with pagination
     */
    public function getGalleryWithPagination(int $limit = 10, int $offset = 0): array
    {
        return $this->galleryRepository->findActiveWithLimit($limit, $offset);
    }

    /**
     * Add a gallery item
     */
    public function addGalleryItem(string $title, string $imagePath, ?string $description = null): Gallery
    {
        $gallery = new Gallery();
        $gallery->setTitle($title);
        $gallery->setImagePath($imagePath);
        $gallery->setDescription($description);
        $gallery->setCreatedAt(new \DateTime());
        $gallery->setActive(true);

        $this->entityManager->persist($gallery);
        $this->entityManager->flush();

        return $gallery;
    }

    /**
     * Update a gallery item
     */
    public function updateGalleryItem(Gallery $gallery, array $data): Gallery
    {
        if (isset($data['title'])) {
            $gallery->setTitle($data['title']);
        }

        if (isset($data['description'])) {
            $gallery->setDescription($data['description']);
        }

        if (isset($data['imagePath'])) {
            $gallery->setImagePath($data['imagePath']);
        }

        if (isset($data['isActive'])) {
            $gallery->setActive($data['isActive']);
        }

        $this->entityManager->flush();

        return $gallery;
    }

    /**
     * Remove a gallery item
     */
    public function removeGalleryItem(Gallery $gallery): void
    {
        $this->entityManager->remove($gallery);
        $this->entityManager->flush();
    }

    /**
     * Get total active items count
     */
    public function getActiveItemsCount(): int
    {
        return count($this->getActiveGallery());
    }
}

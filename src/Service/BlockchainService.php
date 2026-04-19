<?php

namespace App\Service;

use App\Entity\BlockchainRecord;
use App\Repository\BlockchainRecordRepository;
use Doctrine\ORM\EntityManagerInterface;

class BlockchainService
{
    public function __construct(
        private EntityManagerInterface $em,
        private BlockchainRecordRepository $blockchainRepo
    ) {}

    /**
     * Crée un nouveau bloc dans la chaîne
     */
    public function createBlock(array $data, string $type = 'DATA'): BlockchainRecord
    {
        $lastBlock = $this->blockchainRepo->findLastBlock();
        $previousHash = $lastBlock?->getDataHash() ?? '0000000000000000000000000000000000000000000000000000000000000000';

        $timestamp = time();
        $nonce = 0;
        
        // Mining simplifié (proof of work léger)
        $hash = $this->mineBlock($data, $previousHash, $timestamp, $nonce);

        $block = new BlockchainRecord();
        $block->setData(array_merge($data, [
            'type' => $type,
            'timestamp' => $timestamp,
            'nonce' => $nonce
        ]));
        $block->setDataHash($hash['hash']);
        $block->setPreviousHash($previousHash);

        $this->em->persist($block);
        $this->em->flush();

        return $block;
    }

    /**
     * Mining avec proof of work simplifié
     */
    private function mineBlock(array $data, string $previousHash, int $timestamp, int &$nonce): array
    {
        $difficulty = 2; // Nombre de zéros requis au début
        $target = str_repeat('0', $difficulty);

        do {
            $hash = $this->calculateHash($data, $previousHash, $timestamp, $nonce);
            $nonce++;
        } while (substr($hash, 0, $difficulty) !== $target);

        return [
            'hash' => $hash,
            'nonce' => $nonce - 1
        ];
    }

    /**
     * Calcule le hash SHA-256
     */
    public function calculateHash(array $data, string $previousHash, int $timestamp, int $nonce): string
    {
        $string = json_encode($data) . $previousHash . $timestamp . $nonce;
        return hash('sha256', $string);
    }

    /**
     * Vérifie l'intégrité complète de la chaîne
     */
    public function verifyChain(): array
    {
        $blocks = $this->blockchainRepo->findAllOrdered();

        if (empty($blocks)) {
            return [
                'valid' => true,
                'message' => 'Chaîne vide',
                'total_blocks' => 0
            ];
        }

        $errors = [];

        for ($i = 1; $i < count($blocks); $i++) {
            $currentBlock = $blocks[$i];
            $previousBlock = $blocks[$i - 1];

            // Vérification 1: Hash précédent correspond
            if ($currentBlock->getPreviousHash() !== $previousBlock->getDataHash()) {
                $errors[] = [
                    'block_id' => $currentBlock->getId(),
                    'type' => 'PREVIOUS_HASH_MISMATCH',
                    'message' => "Le hash précédent du bloc #{$currentBlock->getId()} ne correspond pas"
                ];
            }

            // Vérification 2: Hash actuel est valide
            $data = $currentBlock->getData();
            $recalculatedHash = $this->calculateHash(
                $data,
                $currentBlock->getPreviousHash(),
                $data['timestamp'] ?? time(),
                $data['nonce'] ?? 0
            );

            if ($recalculatedHash !== $currentBlock->getDataHash()) {
                $errors[] = [
                    'block_id' => $currentBlock->getId(),
                    'type' => 'HASH_INVALID',
                    'message' => "Le hash du bloc #{$currentBlock->getId()} est invalide"
                ];
            }
        }

        return [
            'valid' => empty($errors),
            'total_blocks' => count($blocks),
            'errors' => $errors,
            'message' => empty($errors) ? 'Chaîne valide et intègre' : 'Anomalies détectées'
        ];
    }

    /**
     * Détails complets de la blockchain
     */
    public function getBlockchainDetails(): array
    {
        $blocks = $this->blockchainRepo->findAllOrdered();
        $stats = $this->blockchainRepo->getStats();
        $verification = $this->verifyChain();
        $activity = $this->blockchainRepo->getActivityByDay();

        return [
            'blocks' => $blocks,
            'stats' => $stats,
            'verification' => $verification,
            'activity' => $activity,
            'chain_length' => count($blocks),
            'genesis_block' => $blocks[0] ?? null,
            'last_block' => $this->blockchainRepo->findLastBlock()
        ];
    }

    /**
     * Récupère un bloc par ID avec vérification
     */
    public function getBlockDetails(int $id): ?array
    {
        $block = $this->em->getRepository(BlockchainRecord::class)->find($id);

        if (!$block) {
            return null;
        }

        $previousBlock = null;
        $nextBlock = null;

        if ($id > 1) {
            $previousBlock = $this->em->getRepository(BlockchainRecord::class)->find($id - 1);
        }

        $nextBlock = $this->em->getRepository(BlockchainRecord::class)->find($id + 1);

        // Vérification du bloc
        $isValid = true;
        $validationErrors = [];

        if ($previousBlock && $block->getPreviousHash() !== $previousBlock->getDataHash()) {
            $isValid = false;
            $validationErrors[] = 'Hash précédent ne correspond pas';
        }

        $data = $block->getData();
        $recalculatedHash = $this->calculateHash(
            $data,
            $block->getPreviousHash(),
            $data['timestamp'] ?? time(),
            $data['nonce'] ?? 0
        );

        if ($recalculatedHash !== $block->getDataHash()) {
            $isValid = false;
            $validationErrors[] = 'Hash du bloc invalide';
        }

        return [
            'block' => $block,
            'previousBlock' => $previousBlock,
            'nextBlock' => $nextBlock,
            'isValid' => $isValid,
            'validationErrors' => $validationErrors
        ];
    }

    /**
     * Recherche dans la blockchain
     */
    public function searchBlocks(string $query): array
    {
        $blocks = $this->blockchainRepo->findAllOrdered();
        $results = [];

        foreach ($blocks as $block) {
            $data = json_encode($block->getData());
            $hash = $block->getDataHash();

            if (
                stripos($data, $query) !== false || 
                stripos($hash, $query) !== false
            ) {
                $results[] = $block;
            }
        }

        return $results;
    }

    /**
     * Export blockchain en JSON
     */
    public function exportToJson(): string
    {
        $blocks = $this->blockchainRepo->findAllOrdered();
        $export = [];

        foreach ($blocks as $block) {
            $export[] = [
                'id' => $block->getId(),
                'hash' => $block->getDataHash(),
                'previousHash' => $block->getPreviousHash(),
                'data' => $block->getData(),
                'timestamp' => $block->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return json_encode([
            'blockchain' => $export,
            'total_blocks' => count($blocks),
            'exported_at' => date('Y-m-d H:i:s'),
            'version' => '1.0'
        ], JSON_PRETTY_PRINT);
    }

    // ===== MÉTHODES UTILITAIRES =====

    public function getTotalBlocks(): int
    {
        return $this->blockchainRepo->getTotalBlocks();
    }

    public function getLastHash(): ?string
    {
        return $this->blockchainRepo->findLastBlock()?->getDataHash();
    }

    public function getLastBlockDate(): ?\DateTimeImmutable
    {
        return $this->blockchainRepo->findLastBlock()?->getCreatedAt();
    }
}
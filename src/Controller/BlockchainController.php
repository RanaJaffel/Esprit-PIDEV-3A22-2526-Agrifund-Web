<?php

namespace App\Controller;

use App\Service\BlockchainService;
use App\Repository\BlockchainRecordRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/blockchain')]
class BlockchainController extends AbstractController
{
    #[Route('/viewer/{idproject}', name: 'blockchain_view')]
    public function viewer(
        int $idproject,
        BlockchainService $blockchainService
    ): Response
    {
        $details = $blockchainService->getBlockchainDetails();

        return $this->render('blockchain/viewer.html.twig', [
            'idproject' => $idproject,
            'blocks' => $details['blocks'],
            'stats' => $details['stats'],
            'verification' => $details['verification'],
            'activity' => $details['activity'],
            'chain_length' => $details['chain_length'],
            'genesis_block' => $details['genesis_block'],
            'last_block' => $details['last_block']
        ]);
    }

    #[Route('/block/{id}', name: 'blockchain_block_details')]
    public function blockDetails(
        int $id,
        BlockchainService $blockchainService
    ): Response
    {
        $details = $blockchainService->getBlockDetails($id);

        if (!$details) {
            throw $this->createNotFoundException('Bloc introuvable');
        }

        return $this->render('blockchain/block_details.html.twig', $details);
    }

    #[Route('/api/verify', name: 'blockchain_api_verify')]
    public function apiVerify(BlockchainService $blockchainService): JsonResponse
    {
        $verification = $blockchainService->verifyChain();
        return $this->json($verification);
    }

    #[Route('/api/create-block', name: 'blockchain_api_create', methods: ['POST'])]
    public function apiCreateBlock(
        Request $request,
        BlockchainService $blockchainService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid data'], 400);
        }

        try {
            $block = $blockchainService->createBlock($data);

            return $this->json([
                'success' => true,
                'block_id' => $block->getId(),
                'hash' => $block->getDataHash()
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/export', name: 'blockchain_export')]
    public function export(BlockchainService $blockchainService): Response
    {
        $json = $blockchainService->exportToJson();

        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment; filename="blockchain_export.json"');

        return $response;
    }

    #[Route('/search', name: 'blockchain_search')]
    public function search(
        Request $request,
        BlockchainService $blockchainService
    ): Response
    {
        $query = $request->query->get('q', '');
        $results = [];

        if ($query) {
            $results = $blockchainService->searchBlocks($query);
        }

        return $this->render('blockchain/search.html.twig', [
            'query' => $query,
            'results' => $results
        ]);
    }
}
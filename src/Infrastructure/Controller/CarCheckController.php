<?php

namespace App\Infrastructure\Controller;

use App\Application\UseCase\CarCheckPaidHandler;
use App\Domain\DTO\CarCheck\Request\CarCheckRequest;
use App\Infrastructure\Soap\Clients\CarCheckClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route('/api/carcheck')]
class CarCheckController extends AbstractController
{
    public function __construct(
        private readonly CarCheckClient $client,
        private readonly CarCheckPaidHandler     $paidHandler,
    ) {}

    #[Route('/free', methods: ['POST'])]
    public function free(#[MapRequestPayload] CarCheckRequest $request): JsonResponse
    {
        try {
            $response = $this->client->carCheckFree($request);

            if ($response->isFound()) {
                return $this->json([
                    'success' => false,
                    'data' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'success' => true,
                'data' => $response,
            ]);
        }
        catch (Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    #[Route('/paid', methods: ['POST'])]
    public function paid(#[MapRequestPayload] CarCheckRequest $request): JsonResponse
    {
        try {
            $response = $this->paidHandler->handle($request);

            return $this->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}

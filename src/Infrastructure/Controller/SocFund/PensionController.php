<?php

namespace App\Infrastructure\Controller\SocFund;

use App\Domain\DTO\SocFund\SocFundInfoRequest;
use App\Infrastructure\Soap\Clients\PensionClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use SoapFault;

#[Route('/api/soc-fund')]
class PensionController extends AbstractController
{
    public function __construct(
        private readonly PensionClient $client,
    ) {}

    /**
     * @throws SoapFault
     */
    #[Route('/pension-info', methods: ['POST'])]
    public function GetPensionInfoWithSum(#[MapRequestPayload] SocFundInfoRequest $request): JsonResponse
    {
        $response = $this->client->GetPensionInfoWithSum($request);

        if ($response->isSuccess()) {
            return $this->json([
                'success' => false,
                'data' => null,
            ]);
        }

        return $this->json([
            'success' => true,
            'data' => $response,
        ]);
    }
}

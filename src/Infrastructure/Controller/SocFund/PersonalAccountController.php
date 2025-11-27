<?php

namespace App\Infrastructure\Controller\SocFund;

use App\Domain\DTO\SocFund\SocFundInfoRequest;
use App\Infrastructure\Soap\Clients\PersonalAccountClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use SoapFault;

#[Route('/api/soc-fund')]
class PersonalAccountController extends AbstractController
{
    public function __construct(
        private readonly PersonalAccountClient $client,
    ) {}

    /**
     * @throws SoapFault
     */
    #[Route('/work-periods-with-sum', methods: ['POST'])]
    public function GetWorkPeriodInfoWithSum(#[MapRequestPayload] SocFundInfoRequest $request): JsonResponse
    {
        $response = $this->client->GetWorkPeriodInfoWithSum($request);

        if (!$response->state) {
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

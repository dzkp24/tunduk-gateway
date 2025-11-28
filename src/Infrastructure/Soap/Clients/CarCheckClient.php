<?php

namespace App\Infrastructure\Soap\Clients;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Domain\DTO\CarCheck\Request\CarCheckRequest;
use App\Domain\DTO\CarCheck\Response\CarCheckHistory;
use App\Domain\DTO\CarCheck\Response\CarCheckResponse;
use App\Domain\DTO\CarCheck\Response\CarCheckVehicle;
use App\Infrastructure\Soap\XRoadHeaderFactory;
use App\Infrastructure\Soap\XRoadSoapClient;
use DateTimeImmutable;
use SoapFault;

readonly class CarCheckClient
{
    private XRoadSoapClient $client;
    private LoggerInterface $logger;

    /**
     * @throws SoapFault
     */
    public function __construct(
        #[Autowire('%env(TUNDUK_LOCATION)%')] string          $location,
        #[Autowire('%env(CARCHECK_WSDL)%')] string            $wsdl,
        #[Autowire('%env(CARCHECK_SERVICE_CODE)%')] string    $serviceCode,
        #[Autowire('%env(CARCHECK_SUBSYSTEM_CODE)%')] string  $serviceSubsystem,
        #[Autowire('%env(CARCHECK_MEMBER_CODE)%')] string     $memberCode,
        #[Autowire('%env(CARCHECK_SERVICE_VERSION)%')] string $serviceVersion,
        XRoadHeaderFactory                                    $headers,
        LoggerInterface                                       $logger
    ) {
        $this->client = new XRoadSoapClient(
            $wsdl,
            $location,
            $serviceSubsystem,
            $serviceCode,
            $memberCode,
            $serviceVersion,
            $headers,
            $logger,
        );

        $this->logger = $logger;
    }

    /**
     * @throws SoapFault
     * @throws Exception
     */
    public function carCheckFree(CarCheckRequest $request): CarCheckResponse
    {
        $soapData = $this->client->call('carCheckFree', [
            'request' => [
                'govPlate' => $request->govPlate,
            ],
        ]);

        $response = $soapData->response ?? null;
        $status = (int) ($response->status ?? 500);

        if ($status !== 200 || empty($response->car)) {
            $this->logger->critical('CarCheck error: Invalid status or empty car data', [
                'status' => $status,
                'response_dump' => $response,
            ]);

            return new CarCheckResponse(status: $status);
        }

        $carData = $response->car;

        $vehicle = new CarCheckVehicle(
            govPlate: (string)$carData->govPlate,
            brand: $carData->brand ?? null,
            model: $carData->model ?? null,
            year: isset($carData->year) ? (int)$carData->year : null,
            color: null,    // В бесплатном ответе поля color нет
            steering: null, // В бесплатном ответе поля steering нет
            engineVolume: isset($carData->engineVolume) ? (int)$carData->engineVolume : null,
            fuelType: $carData->motorType ?? null,
            specialNotes: $carData->specialNotes ?? null,

            tintingValidUntil: !empty($carData->tintingWindow)
                ? new DateTimeImmutable($carData->tintingWindow)
                : null
        );

        return new CarCheckResponse(
            status: $status,
            vehicle: $vehicle,

            // В бесплатной версии эти данные отсутствуют:
            hasArrest: false,
            hasPenalty: false,
            mileage: null,
            photos: [],
            history: [],
            isPaidReportAvailable: (bool)($carData->paidVersion ?? false)
        );
    }

    /**
     * @throws SoapFault
     * @throws Exception
     */
    public function carCheckPaid(string $paymentCode): CarCheckResponse
    {
        $soapData = $this->client->call('carCheckPaid', [
            'paymentNumber' => $paymentCode,
        ]);

        $response = $soapData->response ?? null;
        $status = (int)($response->status ?? 500);

        if ($status !== 200 || empty($response->car)) {
            return new CarCheckResponse(status: $status);
        }

        $carData = $response->car;

        $vehicle = new CarCheckVehicle(
            govPlate: (string)$carData->govPlate,
            brand: $carData->brand ?? null,
            model: $carData->model ?? null,
            year: isset($carData->year) ? (int)$carData->year : null,
            color: $carData->color ?? null,
            steering: $carData->steering ?? null,
            engineVolume: isset($carData->engineVolume) ? (int)$carData->engineVolume : null,

            fuelType: null,
            specialNotes: null,
            tintingValidUntil: null
        );

        return new CarCheckResponse(
            status: $status,
            vehicle: $vehicle,
            hasArrest: (bool)($response->arrest ?? false),
            hasPenalty: false, // Этот сервис не возвращает штрафы, только аресты
            mileage: isset($response->mileage) ? (int)$response->mileage : null,
            photos: $this->photoProcessing($response->photos ?? null),
            history: $this->historyProcessing($respons->peroid ?? null),
            isPaidReportAvailable: true, // Отчет уже куплен
            paymentCode: $paymentCode,
        );
    }

    /**
     * @throws SoapFault
     */
    public function generatePaymentCode(CarCheckRequest $request): ?string
    {
        $soapData = $this->client->call('carCheckGeneratePayment', [
            'govPlate' => $request->govPlate
        ],
        );

        $response = $soapData->response ?? null;
        $status = (int)($response->status ?? 500);

        if ($status === 200 && !empty($response->paymentNumber)) {
            return (string)$response->paymentNumber;
        }

        return null;
    }

    private function photoProcessing(mixed $responsePhotos): array
    {
        $photos = [];
        $rawPhotos = $this->ensureArray($responsePhotos ?? []);

        foreach ($rawPhotos as $photoObj) {
            if (isset($photoObj->src) && is_string($photoObj->src)) {
                $photos[] = $photoObj->src;
            }
        }

        return $photos;
    }

    /**
     * @throws Exception
     */
    private function historyProcessing(mixed $responseHistory): array
    {
        $history = [];
        $rawPeriods = $this->ensureArray($responseHistory ?? []);

        foreach ($rawPeriods as $period) {
            $history[] = new CarCheckHistory(
                dateFrom: !empty($period->dateFrom) ? new DateTimeImmutable($period->dateFrom) : null,
                dateTo: !empty($period->dateTo) ? new DateTimeImmutable($period->dateTo) : null
            );
        }

        return $history;
    }

    private function ensureArray(mixed $data): array
    {
        if (empty($data)) {
            return [];
        }

        if (is_array($data)) {
            return $data;
        }

        return [$data];
    }
}

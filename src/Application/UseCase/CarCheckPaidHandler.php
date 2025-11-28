<?php

namespace App\Application\UseCase;

use App\Domain\DTO\CarCheck\Request\CarCheckRequest;
use App\Domain\DTO\CarCheck\Response\CarCheckResponse;
use App\Infrastructure\Soap\Clients\CarCheckClient;
use RuntimeException;
use SoapFault;

readonly class CarCheckPaidHandler
{
    public function __construct(
        private CarCheckClient $client,
    ) {}

    /**
     * @throws SoapFault
     */
    public function handle(CarCheckRequest $request): CarCheckResponse
    {
        $paymentCode = $this->client->generatePaymentCode($request);

        if (is_null($paymentCode)) {
            throw new RuntimeException('Не получилось сгенерировать код оплаты.');
        }

        return $this->client->carCheckPaid($paymentCode);
    }
}

<?php

namespace App\Infrastructure\Soap\Clients;

use App\Domain\DTO\SocFund\SocFundInfoRequest;
use App\Domain\DTO\SocFund\WorkPeriodWithSumItem;
use App\Domain\DTO\SocFund\WorkPeriodWithSumResponse;
use App\Infrastructure\Soap\XRoadHeaderFactory;
use App\Infrastructure\Soap\XRoadSoapClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use SoapFault;

readonly class PersonalAccountClient
{
    private XRoadSoapClient $client;

    /**
     * @throws SoapFault
     */
    public function __construct(
        #[Autowire('%env(TUNDUK_LOCATION)%')] string                  $location,
        #[Autowire('%env(PERSONAL_ACCOUNT_WSDL)%')] string            $wsdl,
        #[Autowire('%env(PERSONAL_ACCOUNT_SERVICE_CODE)%')] string    $serviceCode,
        #[Autowire('%env(PERSONAL_ACCOUNT_SUBSYSTEM_CODE)%')] string  $serviceSubSystem,
        #[Autowire('%env(PERSONAL_ACCOUNT_MEMBER_CODE)%')] string     $memberCode,
        #[Autowire('%env(PERSONAL_ACCOUNT_SERVICE_VERSION)%')] string $serviceVersion,
        XRoadHeaderFactory                                            $headers,
    ) {
        $this->client = new XRoadSoapClient(
            $location,
            $wsdl,
            $serviceSubSystem,
            $serviceCode,
            $memberCode,
            $serviceVersion,
            $headers,
        );
    }

    /**
     * @throws SoapFault
     */
    public function GetWorkPeriodInfoWithSum(SocFundInfoRequest $request): WorkPeriodWithSumResponse
    {
        $soapData = $this->client->call('GetWorkPeriodInfoWithSum', [
            'PIN' => $request->pin,
            'RequestOrg' => $request->requestOrg,
            'RequestPerson' => $request->requestPerson,
        ]);

        $response = $soapData->GetWorkPeriodInfoWithSumResponse ?? null;

        if (!$response) {
            return new WorkPeriodWithSumResponse(null, null, null, null, 'NO_RESPONSE');
        }

        $workPeriods = $this->ensureArray($response->WorkPeriods->WorkPeriodWithSum ?? []);

        return new WorkPeriodWithSumResponse(
            pin: $response->PIN ?? null,
            firstName: $response->FirstName ?? null,
            lastName: $response->LastName ?? null,
            patronymic: $response->Patronymic ?? null,
            state: $response->State ?? null,
            periods: $this->mapWorkPeriods($workPeriods),
        );
    }

    private function mapWorkPeriods(array $workPeriods): array
    {
        $periods = [];

        foreach ($workPeriods as $workPeriod) {
            $periods[] = new WorkPeriodWithSumItem(
                payer: $workPeriod->Payer ?? null,
                inn: $workPeriod->INN ?? null,
                numSf: $workPeriod->NumSF ?? null,
                dateBegin: $workPeriod->DateBegin ?? null,
                dateEnd: $workPeriod->DateEnd ?? null,
                salary: $workPeriod->Salary ?? 0.0
            );
        }

        return $periods;
    }

    private function ensureArray(mixed $data): array
    {
        if ($data === null) {
            return [];
        }
        if (is_array($data)) {
            return $data;
        }
        return [$data];
    }
}

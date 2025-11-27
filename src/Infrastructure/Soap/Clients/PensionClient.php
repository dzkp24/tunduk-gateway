<?php

namespace App\Infrastructure\Soap\Clients;

use App\Domain\DTO\SocFund\Response\DossierInfo;
use App\Domain\DTO\SocFund\Response\PensionInfoResponse;
use App\Domain\DTO\SocFund\Response\PinInfo;
use App\Domain\DTO\SocFund\SocFundInfoRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Infrastructure\Soap\XRoadHeaderFactory;
use App\Infrastructure\Soap\XRoadSoapClient;
use SoapFault;

readonly class PensionClient
{
    private XRoadSoapClient $client;

    /**
     * @throws SoapFault
     */
    public function __construct(
        #[Autowire('%env(TUNDUK_LOCATION)%')] string         $location,
        #[Autowire('%env(PENSION_WSDL)%')] string            $wsdl,
        #[Autowire('%env(PENSION_SERVICE_CODE)%')] string    $serviceCode,
        #[Autowire('%env(PENSION_SUBSYSTEM_CODE)%')] string  $serviceSubSystem,
        #[Autowire('%env(PENSION_MEMBER_CODE)%')] string     $memberCode,
        #[Autowire('%env(PENSION_SERVICE_VERSION)%')] string $serviceVersion,
        XRoadHeaderFactory                                   $headers,
        LoggerInterface                                      $logger
    ) {
        $this->client = new XRoadSoapClient(
            $location,
            $wsdl,
            $serviceSubSystem,
            $serviceCode,
            $memberCode,
            $serviceVersion,
            $headers,
            $logger,
        );
    }

    /**
     * @throws SoapFault
     */
    public function GetPensionInfoWithSum(SocFundInfoRequest $request): PensionInfoResponse
    {
        $soapData = $this->client->call('GetPensionInfo', [
            'PIN' => $request->pin,
            'RequestOrg' => $request->requestOrg,
            'RequestPerson' => $request->requestPerson,
        ]);

        $response = $soapData->GetPensionInfoResponse ?? null;

        if (!$response) {
            return new PensionInfoResponse(causeOfError: 'INTERNAL_ERROR');
        }

        return new PensionInfoResponse(
            causeOfError: (string)($response->CauseOfError ?? 'INTERNAL_ERROR'),
            pinInfos: $this->mapPinInfos($response->PINInfoes->PINInfo ?? []),
            dossiers: $this->mapDossiers($response->DossierInfoes->DossierInfo ?? [])
        );
    }

    private function mapPinInfos(mixed $data): array
    {
        $result = [];
        $items = $this->ensureArray($data);

        foreach ($items as $item) {
            $result[] = new PinInfo(
                state: $item->State ?? 0,
                pin: $item->PIN ?? '',
                firstName: $item->FirstName ?? null,
                lastName: $item->LastName ?? null,
                fullName: $item->FullName ?? null,
                data: $item->Date ?? null,
                issuer: $item->Issuer ?? null
            );
        }
        return $result;
    }

    private function mapDossiers(mixed $data): array
    {
        $result = [];
        $items = $this->ensureArray($data);

        foreach ($items as $item) {
            $result[] = new DossierInfo(
                rusf: $item->RUSF ?? null,
                numDossier: $item->NumDossier ?? null,
                pinPensioner: $item->PINPensioner ?? null,
                pinRecipient: $item->PINRecipient ?? null,
                dateFromInitial: $item->DateFromInitial ?? null,
                dateTo: $item->DateTo ?? null,
                sum: $item->Sum ?? null,
                kindOfPension: $item->KindOfPension ?? 0,
                categoryPension: $item->CategoryPension ?? null,
                pin1: $item->PIN1 ?? null,
                pin2: $item->PIN2 ?? null
            );
        }
        return $result;
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

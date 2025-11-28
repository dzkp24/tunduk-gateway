<?php

namespace App\Infrastructure\Soap;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Ramsey\Uuid\Uuid;
use SoapHeader;
use SoapVar;
use stdClass;

class XRoadHeaderFactory
{
    private const NS = 'http://x-road.eu/xsd/xroad.xsd';
    private const ID_NS = 'http://x-road.eu/xsd/identifiers';

    public function __construct(
        #[Autowire('%env(TUNDUK_CLIENT_CLASS)%')]
        private readonly string $memberClass,

        #[Autowire('%env(TUNDUK_CLIENT_CODE)%')]
        private readonly string $memberCode,

        #[Autowire('%env(TUNDUK_CLIENT_SUBSYSTEM)%')]
        private readonly string $subSystemCode,
    ) {}

    public function make(
        string $serviceSybSystem,
        string $serviceCode,
        string $memberCode,
        string $instance = 'central-server',
        string $memberClass = 'GOV',
        string $serviceVersion = 'v1'
    ): array {
        $clientData = new stdClass();
        $clientData->xRoadInstance = $instance;
        $clientData->memberClass   = $this->memberClass;
        $clientData->memberCode    = $this->memberCode;
        $clientData->subsystemCode = $this->subSystemCode;
        $clientData->objectType    = 'SUBSYSTEM';

        $clientVar = new SoapVar(
            $clientData,
            SOAP_ENC_OBJECT,
            'XRoadClientIdentifierType',
            self::ID_NS
        );

        $serviceData = new stdClass();
        $serviceData->xRoadInstance = $instance;
        $serviceData->memberClass   = $memberClass;
        $serviceData->memberCode    = $memberCode;
        $serviceData->subsystemCode = $serviceSybSystem;
        $serviceData->serviceCode   = $serviceCode;
        $serviceData->serviceVersion = $serviceVersion;
        $serviceData->objectType    = 'SERVICE';

        $serviceVar = new SoapVar(
            $serviceData,
            SOAP_ENC_OBJECT,
            'XRoadServiceIdentifierType',
            self::ID_NS
        );

        return [
            new SoapHeader(self::NS, 'client', $clientVar),
            new SoapHeader(self::NS, 'service', $serviceVar),
            new SoapHeader(self::NS, 'id', Uuid::uuid4()->toString(), true),
            new SoapHeader(self::NS, 'userId', 'DFA', true),
            new SoapHeader(self::NS, 'issue', 'GatewayRequest', true),
            new SoapHeader(self::NS, 'protocolVersion', '4.0', true),
        ];
    }
}

<?php

namespace App\Infrastructure\Soap;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Ramsey\Uuid\Uuid;
use SoapHeader;

class XRoadHeaderFactory
{
    private const NS = 'http://x-road.eu/xsd/xroad.xsd';

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
        string $serviceVersion = 'v1'): array
    {
        return [
            new SoapHeader(self::NS, 'client', [
                'objectType' => 'SUBSYSTEM',
                'xRoadInstance' => $instance,
                'memberClass' => $this->memberClass,
                'memberCode' => $this->memberCode,
                'subsystemCode' => $this->subSystemCode,
            ]),

            new SoapHeader(self::NS, 'service', [
                'objectType' => 'SERVICE',
                'xRoadInstance' => $instance,
                'memberClass' => $memberClass,
                'memberCode' => $memberCode,
                'subsystemCode' => $serviceSybSystem,
                'serviceCode' => $serviceCode,
                'serviceVersion' => $serviceVersion,
            ]),

            new SoapHeader(self::NS, 'id', Uuid::uuid4()->toString(), true),
            new SoapHeader(self::NS, 'userId', 'DFA', true),
            new SoapHeader(self::NS, 'protocolVersion', '4.0', true),
            new SoapHeader(self::NS, 'issue', 'GatewayRequest', true),
        ];
    }
}

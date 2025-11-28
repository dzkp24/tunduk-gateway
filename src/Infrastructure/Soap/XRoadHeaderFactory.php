<?php

namespace App\Infrastructure\Soap;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Ramsey\Uuid\Uuid;
use SoapHeader;
use SoapVar;

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
        private readonly string $subsystemCode,
    ) {}

    public function make(
        string $serviceSybSystem,
        string $serviceCode,
        string $memberCode,
        string $instance = 'central-server',
        string $memberClass = 'GOV',
        string $serviceVersion = 'v1'
    ): array {
        $clientXml = sprintf(
            '<xrd:client id:objectType="SUBSYSTEM" xmlns:xrd="%s" xmlns:id="%s">' .
            '<id:xRoadInstance>%s</id:xRoadInstance>' .
            '<id:memberClass>%s</id:memberClass>' .
            '<id:memberCode>%s</id:memberCode>' .
            '<id:subsystemCode>%s</id:subsystemCode>' .
            '</xrd:client>',
            self::NS,
            self::ID_NS,
            $instance,
            $this->memberClass,
            $this->memberCode,
            $this->subsystemCode
        );

        $clientVar = new SoapVar($clientXml, XSD_ANYXML);

        $serviceVersionTag = '';
        if (!empty($serviceVersion)) {
            $serviceVersionTag = sprintf('<id:serviceVersion>%s</id:serviceVersion>', $serviceVersion);
        }

        $serviceXml = sprintf(
            '<xrd:service id:objectType="SERVICE" xmlns:xrd="%s" xmlns:id="%s">' .
            '<id:xRoadInstance>%s</id:xRoadInstance>' .
            '<id:memberClass>%s</id:memberClass>' .
            '<id:memberCode>%s</id:memberCode>' .
            '<id:subsystemCode>%s</id:subsystemCode>' .
            '<id:serviceCode>%s</id:serviceCode>' .
            '%s' .
            '</xrd:service>',
            self::NS,
            self::ID_NS,
            $instance,
            $memberClass,
            $memberCode,
            $serviceSybSystem,
            $serviceCode,
            $serviceVersionTag
        );

        $serviceVar = new SoapVar($serviceXml, XSD_ANYXML);

        return [
            new SoapHeader(self::NS, 'client', $clientVar),
            new SoapHeader(self::NS, 'service', $serviceVar),
            new SoapHeader(self::NS, 'id', Uuid::uuid4()->toString()),
            new SoapHeader(self::NS, 'userId', 'DFA'),
            new SoapHeader(self::NS, 'issue', 'GatewayRequest'),
            new SoapHeader(self::NS, 'protocolVersion', '4.0'),
        ];
    }
}

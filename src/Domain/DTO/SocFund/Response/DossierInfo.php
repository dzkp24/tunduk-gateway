<?php

namespace App\Domain\DTO\SocFund\Response;

class DossierInfo
{
    public function __construct(
        public ?string $rusf,
        public ?string $numDossier,
        public ?string $pinPensioner,
        public ?string $pinRecipient,
        public ?string $dateFromInitial,
        public ?string $dateTo,
        public ?float $sum,
        public int $kindOfPension,
        public ?string $categoryPension,
        public ?string $pin1,
        public ?string $pin2,
    ) {}
}

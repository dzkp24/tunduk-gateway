<?php

namespace App\Domain\DTO\SocFund;

class WorkPeriodWithSumItem
{
    public function __construct(
        public ?string $payer,
        public ?string $inn,
        public ?string $numSf,
        public ?string $dateBegin,
        public ?string $dateEnd,
        public float $salary,
    ) {}
}

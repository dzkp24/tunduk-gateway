<?php

namespace App\Domain\DTO\SocFund;

class WorkPeriodWithSumResponse
{
    public function __construct(
        public ?string $pin,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $patronymic,
        public ?string $state,

        /** @var WorkPeriodWithSumItem[] */
        public array $periods = [],
    ) {}

    public function getFullName(): string
    {
        return trim(sprintf('%s %s %s', $this->lastName, $this->firstName, $this->patronymic));
    }
}

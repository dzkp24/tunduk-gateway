<?php

namespace App\Domain\DTO\CarCheck\Response;

use DateTimeImmutable;

readonly class CarCheckHistory
{
    public function __construct(
        public ?DateTimeImmutable $dateFrom,
        public ?DateTimeImmutable $dateTo,
    ) {}
}

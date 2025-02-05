<?php

namespace App\Dto;

use Ramsey\Uuid\Uuid;

final readonly class CoasterDto implements \JsonSerializable
{
    public function __construct(
        public string $coasterId,
        public int $numberOfWorkers,
        public int $numberOfClients,
        public int $lengthOfTrails,
        public \DateTime $fromHours,
        public \DateTime $toHours,
    ) {}

    public static function fromJson(string $json): CoasterDto
    {
        return self::fromStdClass(json_decode($json));
    }

    public static function fromStdClass(\stdClass $coasterStdClass): CoasterDto
    {
        if (!isset($coasterStdClass->coasterId)) {
            $coasterStdClass->coasterId = Uuid::uuid4()->toString();
        }

        return new CoasterDto(
            (string) $coasterStdClass->coasterId,
            (int) $coasterStdClass->liczba_personelu,
            (int) $coasterStdClass->liczba_klientow,
            (int) $coasterStdClass->dl_trasy,
            new \DateTime($coasterStdClass->godziny_od),
            new \DateTime($coasterStdClass->godziny_do),
        );
    }

    public function jsonSerialize(): object
    {
        return (object) [
            'coasterId' => $this->coasterId,
            'numberOfWorkers' => $this->numberOfWorkers,
            'numberOfClients' => $this->numberOfClients,
            'lengthOfTrails' => $this->lengthOfTrails,
            'fromHours' => $this->fromHours,
            'toHours' => $this->toHours,
        ];
    }
}
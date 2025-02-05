<?php

declare(strict_types=1);

namespace App\Dto;

use Ramsey\Uuid\Uuid;

final readonly class WagonDto implements \JsonSerializable
{
    public function __construct(
        public string $coasterId,
        public string $wagonId,
        public int $numberOfSeats,
        public float $velocity
    ) {}

    public static function fromJson(string $json): WagonDto
    {
        return self::fromStdClass(json_decode($json));
    }

    public static function fromStdClass(\stdClass $wagonStdClass): WagonDto
    {
        if (!isset($wagonStdClass->wagonId)) {
            $wagonStdClass->wagonId = Uuid::uuid4()->toString();
        }

        return new WagonDto(
            (string) $wagonStdClass->coasterId,
            (string) $wagonStdClass->wagonId,
            (int) $wagonStdClass->ilosc_miejsc,
            (float) $wagonStdClass->predkosc_wagonu
        );
    }

    public function jsonSerialize(): object
    {
        return (object) [
            'coasterId' => $this->coasterId,
            'wagonId' => $this->wagonId,
            'numberOfSeats' => $this->numberOfSeats,
            'velocity' => $this->velocity,
        ];
    }
}
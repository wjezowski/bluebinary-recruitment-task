<?php

declare(strict_types=1);

namespace App\Services\Monitoring;

use App\Dto\CoasterDto;
use App\Dto\WagonDto;
use App\Services\Monitoring\Common\CoasterComputerInterface;
use App\Services\Monitoring\Common\CoasterProblemException;
use App\Services\Monitoring\Common\WagonsHelperTrait;
use App\Services\Monitoring\Computers\MaxNumberOfClientsComputer;
use App\Services\Monitoring\Computers\MinNumberOfWagonsComputer;
use App\Services\Monitoring\Computers\MinNumberOfWorkersComputer;

final class CoasterMonitoringService
{
    use WagonsHelperTrait;

    public const int WAGON_5_MINUTES_BREAK = 5 * 60;
    public const string REDIS_MONITORING_CHANNEL = 'monitoring_channel';

    private array $problems = [];

    /** @var CoasterComputerInterface[] $computers */
    private array $computers;

    public function __construct()
    {
        $this->computers[MaxNumberOfClientsComputer::class] = new MaxNumberOfClientsComputer();
        $this->computers[MinNumberOfWagonsComputer::class] = new MinNumberOfWagonsComputer();
        $this->computers[MinNumberOfWorkersComputer::class] = new MinNumberOfWorkersComputer();
    }

    /**
     * @param WagonDto[] $arrayOfWagonDto
     * @return string[]
     */
    public function monitor(CoasterDto $coasterDto, array $arrayOfWagonDto): array
    {
        $numberOfWagons = sizeof($arrayOfWagonDto);

        try {
            $problems = $this->findProblems(
                $results = $this->computeAll($coasterDto, $arrayOfWagonDto),
                $coasterDto,
                $numberOfWagons
            );
        } catch (\DivisionByZeroError) {
            return [
                $this->prepareCoasterIdLine($coasterDto->coasterId),
                $this->prepareWorkingHoursLine($coasterDto->fromHours, $coasterDto->toHours),
                $this->prepareNumberOfWagonsLine($numberOfWagons, '?'),
                $this->prepareNumberOfWorkersLine($coasterDto->numberOfWorkers, '?'),
                $this->prepareNumberOfClientsLine($coasterDto->numberOfClients, '?'),
            ];
        }

        return [
            $this->prepareCoasterIdLine($coasterDto->coasterId),
            $this->prepareWorkingHoursLine($coasterDto->fromHours, $coasterDto->toHours),
            $this->prepareNumberOfWagonsLine($numberOfWagons, $results[MinNumberOfWagonsComputer::class]),
            $this->prepareNumberOfWorkersLine($coasterDto->numberOfWorkers, $results[MinNumberOfWorkersComputer::class]),
            $this->prepareNumberOfClientsLine($coasterDto->numberOfClients, $results[MaxNumberOfClientsComputer::class]),
            $this->prepareStatusLine($problems),
        ];
    }

    private function prepareCoasterIdLine(string $coasterId): string
    {
        return "0. Id kolejki: $coasterId";
    }

    private function prepareWorkingHoursLine(\DateTime $fromHours, \DateTime $toHours): string
    {
        return "1. Godziny działania: {$fromHours->format('H:i')} - {$toHours->format('H:i')}";
    }

    private function prepareNumberOfWagonsLine(int $realNumberOfWagons, int|string $requiredNumberOfWagons): string
    {
        return "2. Liczba wagonów: $realNumberOfWagons/$requiredNumberOfWagons";
    }

    private function prepareNumberOfWorkersLine(int $realNumberOfWorkers, int|string $requiredNumberOfWorkers): string
    {
        return "3. Dostępny personel: $realNumberOfWorkers/$requiredNumberOfWorkers";
    }

    private function prepareNumberOfClientsLine(int $wantedNumberOfClients, int|string $maxNumberOfClients): string
    {
        return "4. Klienci dziennie: $wantedNumberOfClients/$maxNumberOfClients";
    }

    private function prepareStatusLine(array $problems): string
    {
        return "5. " . (
            empty($problems)
                ? "Status: OK"
                : ("Problem: " . implode('; ', $problems) . '.')
            ) . "\n";
    }

    /**
     * @param WagonDto[] $arrayOfWagonDto
     */
    private function computeAll(CoasterDto $coasterDto, array $arrayOfWagonDto): array
    {
        $results = [];

        foreach ($this->computers as $computerClassName => $computerInstance) {
            $results[$computerClassName] = $computerInstance->compute($coasterDto, $arrayOfWagonDto);
        }

        return $results;
    }

    private function findProblems(array $results, CoasterDto $coasterDto, int $numberOfWagons): array
    {
        $problems = [];

        try {
            //$this->checkNumberOfClientsProblem verifies $this->checkNumberOfWorkersProblem as well.
            //Because when we have problem with number of clients then we have problem with number of workers too.
            $this->checkNumberOfClientsProblem($results, $coasterDto, $numberOfWagons);

            //But we also have to check problem with number of workers even if we haven't problem with number of clients.
            $this->checkNumberOfWorkersProblem($results, $coasterDto);
        } catch (CoasterProblemException $exception) {
            $problems[] = $exception->getMessage();

            \log_message('warning', $exception->getMessage());
        }

        return $problems;
    }

    /**
     * @throws CoasterProblemException
     */
    private function checkNumberOfWorkersProblem(array $results, CoasterDto $coasterDto): void
    {
        $numberOfWorkersDifference = $results[MinNumberOfWorkersComputer::class] - $coasterDto->numberOfWorkers;

        if ($numberOfWorkersDifference > 0) {
            throw new CoasterProblemException("Brakuje $numberOfWorkersDifference pracowników");
        } else if ($numberOfWorkersDifference < 0) {
            throw new CoasterProblemException("Za dużo pracowników o " . abs($numberOfWorkersDifference));
        }
    }

    /**
     * @throws CoasterProblemException
     */
    private function checkNumberOfClientsProblem(array $results, CoasterDto $coasterDto, int $numberOfWagons): void
    {
        $numberOfWagonsDifference = $results[MinNumberOfWagonsComputer::class] - $numberOfWagons;

        $realNumberOfWorkers = $coasterDto->numberOfWorkers;
        $realRequiredNumberOfWorkers = $this->calculateRealRequiredNumberOfWorkersAfterWagonsDifference($results, $numberOfWagons);

        if ($coasterDto->numberOfClients > $results[MaxNumberOfClientsComputer::class]) {
            if ($numberOfWagonsDifference > 0) {
                throw new CoasterProblemException("Do obsługi klientów brakuje $numberOfWagonsDifference wagonów i " . abs($realRequiredNumberOfWorkers - $realNumberOfWorkers) . ' pracowników');
            }
        }

        if ($results[MaxNumberOfClientsComputer::class] > 2 * $coasterDto->numberOfClients) {
            if ($numberOfWagonsDifference < 0) {
                throw new CoasterProblemException('Znaleziono ponad dwukrotną przepustowość klientów - za dużo wagonów o ' . abs($numberOfWagonsDifference) . ' i pracowników o ' . abs($realRequiredNumberOfWorkers - $realNumberOfWorkers));
            }
        }
    }

    private function calculateRealRequiredNumberOfWorkersAfterWagonsDifference(array $results, int $numberOfWagons): int
    {
        /** @var MinNumberOfWorkersComputer $minNumberOfWorkersComputer */
        $minNumberOfWorkersComputer = $this->computers[MinNumberOfWorkersComputer::class];

        $numberOfWagonsDifference = $results[MinNumberOfWagonsComputer::class] - $numberOfWagons;

        $lessThanRequiredNumberOfWorkers = $minNumberOfWorkersComputer->computeNumberOfWorkersForNumberOfWagons(abs($numberOfWagonsDifference));

        return $results[MinNumberOfWorkersComputer::class] - $lessThanRequiredNumberOfWorkers;
    }
}
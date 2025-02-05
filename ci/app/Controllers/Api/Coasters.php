<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Dto\CoasterDto;
use App\Services\Monitoring\Model\MonitoringCoastersModelAdapter;
use CodeIgniter\HTTP\ResponseInterface;

final class Coasters extends BaseController
{
    public function post(): ResponseInterface
    {
        try {
            $coasterDto = CoasterDto::fromJson($this->request->getBody());
        } catch (\Throwable $throwable) {
            helper('environment');

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => is_development() ? $throwable->getMessage() : 'BAD REQUEST']);
        }

        return $this->handleCoasterDto($coasterDto);
    }

    public function put(string $coasterId): ResponseInterface
    {
        $coasterStdClass = json_decode($this->request->getBody());
        $coasterStdClass->coasterId = $coasterId;

        try {
            $coasterDto = CoasterDto::fromStdClass($coasterStdClass);
        } catch (\Throwable $throwable) {
            helper('environment');

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => is_development() ? $throwable->getMessage() : 'BAD REQUEST']);
        }

        return $this->handleCoasterDto($coasterDto);
    }

    private function handleCoasterDto(CoasterDto $coasterDto): ResponseInterface
    {
        /** @var MonitoringCoastersModelAdapter $coastersModel */
        $coastersModel = model(MonitoringCoastersModelAdapter::class);

        try {
            $coastersModel->saveCoaster($coasterDto);
        } catch (\Throwable $throwable) {
            helper('environment');

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['message' => is_development() ? $throwable->getMessage() : 'INTERNAL SERVER ERROR']);
        }

        return $this->response->setJSON(['message' => 'OK', 'coasterId' => $coasterDto->coasterId]);
    }
}
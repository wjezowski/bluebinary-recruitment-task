<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Dto\WagonDto;
use App\Services\Monitoring\Model\MonitoringWagonsModelAdapter;
use CodeIgniter\HTTP\ResponseInterface;

final class Wagons extends BaseController
{
    private MonitoringWagonsModelAdapter $wagonsModel;

    public function __construct()
    {
        $this->wagonsModel = model(MonitoringWagonsModelAdapter::class);
    }

    public function post(string $coasterId): ResponseInterface
    {
        $wagonStdClass = json_decode($this->request->getBody());
        $wagonStdClass->coasterId = $coasterId;

        try {
            $wagonDto = WagonDto::fromStdClass($wagonStdClass);
        } catch (\Throwable $throwable) {
            helper('environment');

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => is_development() ? $throwable->getMessage() : 'BAD REQUEST']);
        }

        try {
            $this->wagonsModel->saveWagon($wagonDto);
        } catch (\Throwable $throwable) {
            helper('environment');

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['message' => is_development() ? $throwable->getMessage() : 'INTERNAL SERVER ERROR']);
        }

        return $this->response->setJSON(['message' => 'OK', 'wagonId' => $wagonDto->wagonId]);
    }

    public function delete(string $coasterId, string $wagonId): ResponseInterface
    {
        try {
            $this->wagonsModel->deleteWagon($coasterId, $wagonId);
        } catch (\Throwable $throwable) {
            helper('environment');

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['message' => is_development() ? $throwable->getMessage() : 'INTERNAL SERVER ERROR']);
        }

        return $this->response->setJSON(['message' => 'OK']);
    }
}
<?php

declare(strict_types=1);

namespace Darkheim\Application\Api;

use Darkheim\Application\CastleSiege\CastleSiege;

final class CastleSiegeApiController
{
    public function render(): void
    {
        header('Content-Type: application/json');

        try {
            $castleSiege = new CastleSiege();
            $siegeData = $castleSiege->siegeData();
            if (!is_array($siegeData)) {
                throw new \RuntimeException((string) lang('error_103'));
            }

            http_response_code(200);
            echo json_encode([
                'TimeLeft' => $siegeData['warfare_stage_timeleft'],
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $ex) {
            http_response_code(500);
            echo json_encode([
                'code' => 500,
                'error' => $ex->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }
    }
}


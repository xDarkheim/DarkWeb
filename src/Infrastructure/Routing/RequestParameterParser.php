<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

use Darkheim\Infrastructure\Runtime\QueryStore;

final class RequestParameterParser
{
    public function parseInto(QueryStore $query): void
    {
        if (!$query->has('request')) {
            return;
        }

        $request = explode('/', (string) $query->get('request', ''));
        foreach (array_chunk($request, 2) as $pair) {
            $key = $pair[0];
            $val = $pair[1] ?? null;
            if ($key === '') {
                continue;
            }

            $query->set(
                $key,
                ($val !== null && $val !== '') ? htmlspecialchars($val) : null
            );
        }
    }
}


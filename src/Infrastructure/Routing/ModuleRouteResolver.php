<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

use Darkheim\Domain\Validator;

final class ModuleRouteResolver
{
    /**
     * @return array{type: 'module'|'submodule', page: string, subpage?: string}
     */
    public function resolve(string $page, ?string $subpage): array
    {
        if (! Validator::hasValue($subpage)) {
            return [
                'type' => 'module',
                'page' => $page,
            ];
        }

        // Keep /news/* routed to the top-level news page.
        if ($page === 'news') {
            return [
                'type' => 'module',
                'page' => 'news',
            ];
        }

        return [
            'type'    => 'submodule',
            'page'    => $page,
            'subpage' => (string) $subpage,
        ];
    }
}

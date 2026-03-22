<?php

declare(strict_types=1);

namespace Darkheim\Application\View;

use Darkheim\Domain\Validator;

/**
 * Renders user-facing notification messages.
 *
 * – toast()  outputs a hidden <span> that toast.js picks up on DOMContentLoaded.
 * – inline() outputs a visible styled <div> (use for in-context notices).
 *
 * Replaces the inline logic inside the global \Darkheim\Application\View\MessageRenderer::toast() / \Darkheim\Application\View\MessageRenderer::inline() helpers.
 */
final class MessageRenderer
{
    private const COLORS = [
        'error'   => ['bg' => 'rgba(40,10,10,.6)',  'border' => '#7a2d2d', 'left' => '#ef5350', 'text' => '#e89090'],
        'success' => ['bg' => 'rgba(10,30,10,.6)',  'border' => '#2d7a2d', 'left' => '#4caf50', 'text' => '#a0e8a0'],
        'warning' => ['bg' => 'rgba(35,24,0,.6)',   'border' => '#7a5a00', 'left' => '#ffa726', 'text' => '#e8c878'],
        'info'    => ['bg' => 'rgba(10,20,38,.6)',  'border' => '#2d4a7a', 'left' => '#42a5f5', 'text' => '#90b8e8'],
    ];

    /**
     * Outputs a hidden toast trigger span.
     * toast.js reads it on DOMContentLoaded and shows a floating notification.
     */
    public static function toast(string $type = 'info', string $message = '', string $title = ''): void
    {
        $toastType = match ($type) {
            'error'   => 'error',
            'success' => 'success',
            'warning' => 'warning',
            default   => 'info',
        };

        $plainMsg = Validator::hasValue($title)
            ? strip_tags($title) . ': ' . strip_tags($message)
            : strip_tags($message);

        $plainMsg = htmlspecialchars($plainMsg, ENT_QUOTES);

        echo '<span class="dh-toast-trigger" data-type="' . $toastType
            . '" data-message="' . $plainMsg . '" style="display:none;"></span>';
    }

    /**
     * Outputs a visible inline styled message block.
     * Use for passive empty-state notices inside panels/cards.
     */
    public static function inline(string $type = 'info', string $message = '', string $title = ''): void
    {
        $c = self::COLORS[$type] ?? self::COLORS['info'];

        $style = 'display:flex;align-items:flex-start;gap:10px;padding:12px 16px;border-radius:6px;'
            . 'background:' . $c['bg'] . ';border:1px solid ' . $c['border']
            . ';border-left:3px solid ' . $c['left'] . ';'
            . 'color:' . $c['text'] . ';font-size:13px;line-height:1.5;margin:6px 0;';

        echo '<div style="' . $style . '">';

        if (Validator::hasValue($title)) {
            echo '<strong>' . htmlspecialchars(strip_tags($title), ENT_QUOTES) . ':</strong>&nbsp;';
        }

        echo htmlspecialchars(strip_tags($message), ENT_QUOTES);
        echo '</div>';
    }
}


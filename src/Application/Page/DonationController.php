<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Infrastructure\View\ViewRenderer;

final class DonationController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        try {
            if (!mconfig('active')) {
                inline_message('error', lang('error_47', true));
                return;
            }
            $this->view->render('donation', [
                'paypalImageUrl' => __PATH_THEME_IMG__ . 'donation/paypal.jpg',
                'paypalUrl'      => __BASE_URL__ . 'donation/paypal/',
            ]);
        } catch (\Exception $ex) {
            inline_message('error', $ex->getMessage());
        }
    }
}

<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Language\Translator;
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
            if (!\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active')) {
                \Darkheim\Application\View\MessageRenderer::inline('error', Translator::phrase('error_47'));
                return;
            }
            $this->view->render('donation', [
                'paypalImageUrl' => __PATH_THEME_IMG__ . 'donation/paypal.jpg',
                'paypalUrl'      => __BASE_URL__ . 'donation/paypal/',
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}

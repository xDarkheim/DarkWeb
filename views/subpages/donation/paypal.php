<?php
/**
 * Donation PayPal subpage view.
 *
 * Variables provided by DonationPaypalSubpageController:
 * - string $pageTitle
 * - string $conversionRate
 * - string $formAction
 * - string $orderId
 * - string $paypalEmail
 * - string $paypalTitle
 * - string $paypalCurrency
 * - string $donationText
 * - string $returnUrl
 * - string $notifyUrl
 * - string $customUserId
 */
?>

<div class="page-title"><span><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></span></div>

<span id="paypal_conversion_rate_value" style="display:none;"><?php echo htmlspecialchars($conversionRate, ENT_QUOTES, 'UTF-8'); ?></span>

<div class="paypal-gateway-container">
    <div class="paypal-gateway-content">
        <div class="paypal-gateway-logo"></div>

        <form action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post">
            <div class="paypal-gateway-form">
                <div>
                    <input type="hidden" name="cmd" value="_xclick">
                    <input type="hidden" name="business" value="<?php echo htmlspecialchars($paypalEmail, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($paypalTitle, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="item_number" value="<?php echo htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="currency_code" value="<?php echo htmlspecialchars($paypalCurrency, ENT_QUOTES, 'UTF-8'); ?>">
                    <label for="amount" class="sr-only">Amount</label>
                    $ <input type="text" name="amount" id="amount" maxlength="3"> <?php echo htmlspecialchars($paypalCurrency, ENT_QUOTES, 'UTF-8'); ?> = <span id="result">0</span> <?php echo htmlspecialchars($donationText, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>

        <div class="paypal-gateway-continue">
            <input type="hidden" name="no_shipping" value="1">
            <input type="hidden" name="shipping" value="0.00">
            <input type="hidden" name="return" value="<?php echo htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="cancel_return" value="<?php echo htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="notify_url" value="<?php echo htmlspecialchars($notifyUrl, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="custom" value="<?php echo htmlspecialchars($customUserId, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="no_note" value="1">
            <input type="hidden" name="tax" value="0.00">
            <input type="submit" name="submit" value="">
        </div>
        </form>
    </div>
</div>

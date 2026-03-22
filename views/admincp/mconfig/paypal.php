<?php
echo '<h1 class="page-header">PayPal Settings</h1>';
?>
<form action="<?php echo htmlspecialchars((string) ($selectedConfigFormAction ?? ""), ENT_QUOTES, "UTF-8"); ?>" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the papal donation gateway.</span></th>
			<td>
				<?php \Darkheim\Application\View\FormFieldRenderer::enabledisableCheckboxes('setting_2',\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>PayPal Sandbox Mode<br/><span>Enable/disable PayPal's IPN testing mode.<br/><br/>More info:<br/><a href="https://developer.paypal.com/" target="_blank">https://developer.paypal.com/</a></span></th>
			<td>
				<?php \Darkheim\Application\View\FormFieldRenderer::enabledisableCheckboxes('setting_3',\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_enable_sandbox'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>PayPal Email<br/><span>PayPal email where you will receive the donations.</span></th>
			<td>
				<label>
					<input class="input-xxlarge" type="text" name="setting_4" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_email'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>PayPal Donations Title<br/><span>Title of the PayPal donation. Example: "Donation for MU Credits".</span></th>
			<td>
				<label>
					<input class="input-xxlarge" type="text" name="setting_5" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_title'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Currency Code<br/><span>List of available PayPal currencies: <a href="https://cms.paypal.com/uk/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_currency_codes" target="_blank">click here</a>.</span></th>
			<td>
				<label>
					<input class="input-xxlarge" type="text" name="setting_6" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_currency'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Return/Cancel URL<br/><span>URL where the client will be redirected to if the donation is canceled or completed.</span></th>
			<td>
				<label>
					<input class="input-xxlarge" type="text" name="setting_7" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_return_url'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>IPN Notify URL<br/><span>URL of Darkheim's PayPal API.<br/><br/> By default, it has to be in: <b>https://YOURWEBSITE.COM/api/paypal.php</b></span></th>
			<td>
				<label>
					<input class="input-xxlarge" type="text" name="setting_8" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_notify_url'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credits Conversion Rate<br/><span>How many game credits are equivalent to 1 of real money currency?<br/><br/>Example:<br/>1 USD = 100 Credits, in this example you would type in the box 100.</span></th>
			<td>
				<label>
					<input class="input-mini" type="text" name="setting_9" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_conversion_rate'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Configuration<br/><span></span></th>
			<td>
				<?php echo $paypalCreditConfigSelect ?? ''; ?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>
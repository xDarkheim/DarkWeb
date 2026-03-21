<div class="footer-container">
	<div class="row">
		<div class="col-xs-12">
			<?php foreach (($footerData['links'] ?? []) as $index => $link): ?>
			<a href="<?php echo htmlspecialchars((string) ($link['url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) ($link['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></a>
			<?php if ($index < count($footerData['links']) - 1): ?>
			<span style="padding:0 5px;">|</span>
			<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-xs-8">
						<p>
							<?php echo htmlspecialchars((string) ($footerData['copyrightText'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> <br />
							<?php echo $footerData['poweredByHtml'] ?? ''; ?>
						</p>
					</div>
		<div class="col-xs-4">
			<?php foreach (($footerData['socialLinks'] ?? []) as $socialLink): ?>
			<div class="col-xs-4 text-center">
				<a href="<?php echo htmlspecialchars((string) ($socialLink['url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="footer-social-link">
					<img src="<?php echo htmlspecialchars((string) ($socialLink['imageUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" width="50px" height="auto" alt="<?php echo htmlspecialchars((string) ($socialLink['alt'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
				</a>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
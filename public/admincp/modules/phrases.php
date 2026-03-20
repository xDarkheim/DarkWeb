<?php
echo '<h1 class="page-header"><i class="bi bi-translate me-2"></i>Language Phrases</h1>';
try {
	if(!is_array($lang)) {
		throw new RuntimeException('Language file is empty.');
	}
	echo '<div class="acp-card"><div class="acp-card-header">Current Language Phrases ('.count($lang).')</div>';
	echo '<table class="table table-hover mb-0"><thead><tr><th style="width:35%">Key</th><th>Value</th></tr></thead><tbody>';
	foreach($lang as $phrase => $value) {
		echo '<tr><td><code>'.$phrase.'</code></td><td>'.htmlspecialchars($value).'</td></tr>';
	}
	echo '</tbody></table></div>';
} catch(Exception $ex) { message('error', $ex->getMessage()); }

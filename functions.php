<?php

define( "NONCE_LIMIT", 60 * 15 );

function monthSelected($values, $month) {
	if (isset($values['publication_month']) && $values['publication_month'] == $month) {
		return 'selected="selected"';
	}
	return '';
}

function noncePasses($nonce) {
	if (time() - $nonce <= NONCE_LIMIT) {
		return true;
	}
	return false;
}

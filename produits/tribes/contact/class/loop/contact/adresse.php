<?php

class extends loop_contact_email
{
	protected

	$table = 'adresse',
	$extraSelect = 'IF(contact_modified,contact_modified,"") AS contact_modified, is_active, is_shared';
}

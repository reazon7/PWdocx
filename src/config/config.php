<?php

return [

	'template_option' => [
		'path' => 'template', // Template location in storage\app folder
	],

	'variable_option' => [
		'prefix' => '${',
		'suffix' => '}',
	],

	'file_option' => [
		'default_name' => 'Document.docx', // Default document file name if not set
		'temp_name' => 'document_result', // Temporary document file name
	],

];

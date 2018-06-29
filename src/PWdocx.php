<?php
namespace REAZON\PWdocx;

use REAZON\PWdocx\Exceptions\ServiceException;
use \PhpOffice\PhpWord\TemplateProcessor;

class PWdocx {

	private $config;
	private $phpWord;

    function __construct(array $config = null, $fileName) {
    	$this->config = isset($config) ? $config : config('pwdocx');

    	$templatePath = storage_path($config['template_option']['path']);
		$this->makePath($templatePath);

		$templateFile = $templatePath . '/' . $fileName;

		$this->phpWord = new TemplateProcessor($templateFile);
    }

    public function setValues(array $array) {
    	$prefix = array_get($this->config, 'variable_option.prefix', '');
    	$suffix = array_get($this->config, 'variable_option.suffix', '');

    	foreach ($array as $key => $value) {
    		$this->phpWord->setValue($prefix . $key . $suffix, $value);
    	}
    }

    public function download($fileName = '') {
    	$defaultName = array_get($this->config, 'file_option.default_name', 'Document');
    	$tempName = array_get($this->config, 'file_option.temp_name', '');

    	$fileName = empty($fileName) ? $defaultName : $fileName;

    	$resultFile = tempnam(sys_get_temp_dir(), $tempName);

		$this->phpWord->saveAs($resultFile);

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . $fileName . '.docx');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($resultFile));
		flush();

		readfile($resultFile);
		unlink($resultFile);
    }

	private function makePath($path) {
		\File::isDirectory($path) or \File::makeDirectory($path, 0777, true, true);
	}

}
<?php
namespace REAZON\PWdocx;

use \PhpOffice\PhpWord\TemplateProcessor;

class PWdocx
{
	private $config;
	private $phpWord;

	function __construct(array $config = null)
	{
		$this->config = isset($config) ? $config : config('pwdocx');

		return $this;
	}

	public function from($fileName, $parentDir = null)
	{
		$templatePath = storage_path('app\\' . array_get($this->config, 'template_option.path', 'template'));
		$this->makePath($templatePath);

		$templateFile = (isset($parentDir) ? $parentDir : $templatePath) . '/' . $fileName;

		if (!\File::exists($templateFile))
			throw new Exception("Template File Not Found!");

		$this->phpWord = new TemplateProcessor($templateFile);

		return $this;
	}

	public function setValues(array $array)
	{
		$prefix = array_get($this->config, 'variable_option.prefix', '');
		$suffix = array_get($this->config, 'variable_option.suffix', '');

		foreach ($array as $key => $value) {
			$this->phpWord->setValue($prefix . $key . $suffix, $value);
		}

		return $this;
	}

	public function download($fileName = '')
	{
		$defaultName = array_get($this->config, 'file_option.default_name', 'Document.docx');
		$tempName = array_get($this->config, 'file_option.temp_name', '');

		$fileName = empty($fileName) ? $defaultName : $fileName;

		$resultFile = tempnam(sys_get_temp_dir(), $tempName);

		$this->phpWord->saveAs($resultFile);

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . $fileName);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($resultFile));
		flush();

		readfile($resultFile);
		unlink($resultFile);
	}

	public function uploadTemplate($uploadName, $fileName = null, $parentDir = null)
	{
		$templatePath = array_get($this->config, 'template_option.path', 'template');
		$this->makePath($templatePath);

		$parentDir = isset($parentDir) ? $parentDir : $templatePath;
		if (isset($fileName))
			\Storage::putFileAs($parentDir, request()->file($uploadName), $fileName);
		else
			\Storage::putFile($parentDir, request()->file($uploadName));
	}

	private function makePath($path)
	{
		\File::isDirectory($path) or \File::makeDirectory($path, 0777, true, true);
	}

}
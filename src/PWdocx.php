<?php

namespace REAZON\PWdocx;

use File;
use Storage;
use Exception;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;

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
		$templatePath = storage_path('app/' . array_get($this->config, 'template_option.path', 'template'));
		$this->makePath($templatePath);

		$templateFile = (isset($parentDir) ? $parentDir : $templatePath) . '/' . $fileName;

		if (!File::exists($templateFile))
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
		$tempName = array_get($this->config, 'file_option.temp_name', 'document_result');

		$fileName = empty($fileName) ? $defaultName : $fileName;

		$resultFile = tempnam(sys_get_temp_dir(), $tempName);

		$this->phpWord->saveAs($resultFile);

		header('Content-Disposition: attachment; filename=' . $fileName);
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($resultFile));
		flush();

		readfile($resultFile);
		unlink($resultFile);
	}

	public function pdf($fileName = '')
	{
		$this->makePDF($fileName, false);
	}

	public function downloadPDF($fileName = '')
	{
		$this->makePDF($fileName, true);
	}

	private function makePDF($fileName, $forceDownload)
	{
		$tempName = array_get($this->config, 'file_option.temp_name', 'document_result');
		$defaultPDFName = array_get($this->config, 'file_option.default_pdf_name', 'Document.pdf');
		$tempPDFName = array_get($this->config, 'file_option.temp_pdf_name', 'pdf_result');

		$fileName = empty($fileName) ? $defaultPDFName : $fileName;

		$resultFile = tempnam(sys_get_temp_dir(), $tempName);
		$resultPDFFile = tempnam(sys_get_temp_dir(), $tempPDFName);

		$this->phpWord->saveAs($resultFile);

		$doc = IOFactory::load($resultFile);
		unlink($resultFile);

		Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
		Settings::setPdfRendererPath('.');

		$xmlWriter = IOFactory::createWriter($doc, 'PDF');

		$xmlWriter->save($resultPDFFile);

		if ($forceDownload)
			header('Content-Disposition: attachment; filename=' . $fileName);
		else
			header("Content-Disposition: inline; filename=" . $fileName);
		header("Content-Type: application/pdf");
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($resultPDFFile));
		flush();

		readfile($resultPDFFile);

		unlink($resultPDFFile);
	}

	public function uploadTemplate($uploadName, $fileName = null, $parentDir = null)
	{
		$templatePath = array_get($this->config, 'template_option.path', 'template');
		$this->makePath($templatePath);

		$parentDir = isset($parentDir) ? $parentDir : $templatePath;
		if (isset($fileName))
			return Storage::putFileAs($parentDir, request()->file($uploadName), $fileName);
		else
			return Storage::putFile($parentDir, request()->file($uploadName));
	}

	private function makePath($path)
	{
		File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
	}
}

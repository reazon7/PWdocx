<?php

namespace REAZON\PWdocx;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class PWdocxClient
{
	private array $config;
	private TemplateProcessor $phpWord;

	function __construct(array $config = null)
	{
		$this->config = isset($config) ? $config : config('pwdocx');

		return $this;
	}

	public function from(string $fileName, string|null $parentDir = null)
	{
		$templatePath = storage_path('app/' . Arr::get($this->config, 'template_option.path', 'template'));
		$this->makePath($templatePath);

		$templateFile = (!empty($parentDir) ? $parentDir : $templatePath) . '/' . $fileName;

		if (!File::exists($templateFile))
			throw new Exception("Template File Not Found!");

		$this->phpWord = new TemplateProcessor($templateFile);

		return $this;
	}

	public function setValue(string $search, string $replace)
	{
		$this->phpWord->setValue($search, $replace);

		return $this;
	}

	public function setValues(array $values)
	{
		$this->phpWord->setValues($values);

		return $this;
	}

	public function setCloneBlockAndSetValues(string $blockname, array $values)
	{
		$this->phpWord->cloneBlock($blockname, count($values), true, false, $values);

		return $this;
	}

	public function setCloneRowAndSetValues(string $rowname, array $values)
	{
		$this->phpWord->cloneRowAndSetValues($rowname, $values);

		return $this;
	}

	public function download(string|null $fileName = null)
	{
		$defaultName = Arr::get($this->config, 'file_option.default_name', 'Document.docx');
		$tempName = Arr::get($this->config, 'file_option.temp_name', 'document_result');

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

	public function uploadTemplate(string $uploadName, string|null $fileName = null, string|null $parentDir = null, $index = null)
	{
		if (request()->hasFile($uploadName)) {
			$fileUpload = request()->file($uploadName);
			if (!empty($index)) $fileUpload = $fileUpload[$index] ?? null;
			if (empty($fileUpload)) return false;

			if ($fileUpload->isValid()) {
				$templatePath = Arr::get($this->config, 'template_option.path', 'template');
				$this->makePath($templatePath);

				$parentDir = !empty($parentDir) ? $parentDir : $templatePath;

				$fileName = $fileName ?? Str::random(40) . '.' . $fileUpload->getClientOriginalExtension();

				return Storage::putFileAs($parentDir, $fileUpload, $fileName);
			}
		}

		return false;
	}

	public function downloadTemplate(string $filename, string|null $name = null, string|null $parentDir = null)
	{
		$templatePath = Arr::get($this->config, 'template_option.path', 'template');
		$this->makePath($templatePath);

		$parentDir = !empty($parentDir) ? $parentDir : $templatePath;
		$name = !empty($name) ? $name . '.' . pathinfo($filename, PATHINFO_EXTENSION) : null;

		return Storage::download("{$parentDir}/{$filename}", $name);
	}

	public function deleteTemplate(string $fileName, string|null $parentDir = null)
	{
		$templatePath = Arr::get($this->config, 'template_option.path', 'template');
		$this->makePath($templatePath);

		$parentDir = !empty($parentDir) ? $parentDir : $templatePath;

		return Storage::delete(collect([$parentDir, $fileName])->whereNotNull()->join('/'));
	}

	private function makePath(string $path)
	{
		File::isDirectory($path) or File::makeDirectory($path);
	}
}

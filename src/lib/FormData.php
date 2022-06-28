<?php

namespace EasyHttp;

/**
 * FormData class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class FormData
{

	/**
	 * @var array
	 */
	private array $files = [];

	/**
	 * FromData constructor.
	 *
	 * @param string|array $file_path
	 */
	public function __construct(string|array $file_path = [])
	{
		if (is_string($file_path)) {
			$file_path = [$file_path];
		}

		foreach ($file_path as $name => $path) {
			$this->addFile($name, $path);
		}
	}

	/**
	 * Get the field has passed through the class
	 *
	 * @return array
	 */
	public function getFiles(): array
	{
		return $this->files ?? [];
	}

	/**
	 * This method will create an array with instances of CURLFile class
	 *
	 * @param string|array $file_path
	 * @return array
	 */
	public static function create(string|array $file_path): array
	{
		return (new FormData($file_path))->getFiles();
	}

	/**
	 * @param string $name
	 * @param string|\CURLFile $file
	 * @return $this
	 */
	public function addFile(string $name, string|\CURLFile $file): FormData
	{
		if ($file instanceof \CURLFile) {
			$this->files[$name] = $file;
			return $this;
		}

		$this->files[$name] = new \CURLFile(
			realpath($file),
			Client::get_file_type($file),
			basename($file)
		);

		return $this;
	}

}
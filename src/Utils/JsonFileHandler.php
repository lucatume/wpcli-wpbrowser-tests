<?php

namespace tad\WPCLI\Utils;


use tad\WPCLI\Exceptions\FileBadFormatException;
use tad\WPCLI\Exceptions\FileContentsException;

class JsonFileHandler {

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @var string
	 */
	protected $fileContents = '';

	/**
	 * @var \stdClass
	 */
	protected $decodedContents;

	/**
	 * @param string $file
	 */
	public function setFile( $file ) {
		$this->file         = $file;
		$this->fileContents = '';

		return $this;
	}

	/**
	 * @param  string $prop
	 * @param string  $key
	 * @param mixed   $value
	 */
	public function addPropertyValue( $prop, $key, $value ) {
		$this->readFileContents();

		$properties = array_keys( get_object_vars( $this->decodedContents ) );

		if ( ! in_array( $prop, $properties ) ) {
			$this->decodedContents->{$prop} = new \stdClass();
		}

		if ( empty( $this->decodedContents->{$prop}->{$key} ) ) {
			$this->decodedContents->{$prop}->{$key} = $value;
		}

		return $this;
	}

	public function readFileContents() {
		if ( empty( $this->fileContents ) ) {
			$this->fileContents = file_get_contents( $this->file );

			if ( empty( $this->fileContents ) ) {
				throw new FileContentsException( "file '{$this->file}' is unexpectedly empty." );
			}

			$this->decodedContents = json_decode( $this->fileContents );

			if ( ! is_object( $this->decodedContents ) ) {
				throw new FileBadFormatException( "file '{$this->file}' does not contain valid JSON data and format." );
			}
		}
	}

	public function write() {
		if ( empty( $this->decodedContents ) ) {
			return true;
		}

		if ( $this->decodedContents == json_decode( $this->fileContents ) ) {
			return true;
		}

//		if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
//			$contents = json_encode( $this->decodedContents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
//		} else {
			$contents = str_replace( '\/', '/', json_encode( $this->decodedContents, JSON_PRETTY_PRINT ) );
//		}


		return (bool) file_put_contents( $this->file, $contents );
	}
}
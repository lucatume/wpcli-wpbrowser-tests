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

		if ( version_compare( PHP_VERSION, '5.4' ) >= 0 ) {
			$contents = json_encode( $this->decodedContents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		} else {
			$contents = str_replace( '\/', '/', $this->format( json_encode( $this->decodedContents ), true, true ) );
		}

		return (bool) file_put_contents( $this->file, $contents );
	}

	// A mere copy of the Composer one
	protected function format( $json, $unescapeUnicode, $unescapeSlashes ) {
		$result      = '';
		$pos         = 0;
		$strLen      = strlen( $json );
		$indentStr   = '    ';
		$newLine     = "\n";
		$outOfQuotes = true;
		$buffer      = '';
		$noescape    = true;
		for ( $i = 0; $i < $strLen; $i ++ ) {
			// Grab the next character in the string
			$char = substr( $json, $i, 1 );
			// Are we inside a quoted string?
			if ( '"' === $char && $noescape ) {
				$outOfQuotes = ! $outOfQuotes;
			}
			if ( ! $outOfQuotes ) {
				$buffer .= $char;
				$noescape = '\\' === $char ? ! $noescape : true;
				continue;
			} elseif ( '' !== $buffer ) {
				if ( $unescapeSlashes ) {
					$buffer = str_replace( '\\/', '/', $buffer );
				}
				if ( $unescapeUnicode && function_exists( 'mb_convert_encoding' ) ) {
					// https://stackoverflow.com/questions/2934563/how-to-decode-unicode-escape-sequences-like-u00ed-to-proper-utf-8-encoded-cha
					$buffer = preg_replace_callback(
						'/(\\\\+)u([0-9a-f]{4})/i', function ( $match ) {
						$l = strlen( $match[1] );
						if ( $l % 2 ) {
							return str_repeat( '\\', $l - 1 ) . mb_convert_encoding(
								pack( 'H*', $match[2] ), 'UTF-8', 'UCS-2BE'
							);
						}

						return $match[0];
					}, $buffer
					);
				}
				$result .= $buffer . $char;
				$buffer = '';
				continue;
			}
			if ( ':' === $char ) {
				// Add a space after the : character
				$char .= ' ';
			} elseif ( ( '}' === $char || ']' === $char ) ) {
				$pos --;
				$prevChar = substr( $json, $i - 1, 1 );
				if ( '{' !== $prevChar && '[' !== $prevChar ) {
					// If this character is the end of an element,
					// output a new line and indent the next line
					$result .= $newLine;
					for ( $j = 0; $j < $pos; $j ++ ) {
						$result .= $indentStr;
					}
				} else {
					// Collapse empty {} and []
					$result = rtrim( $result );
				}
			}
			$result .= $char;
			// If the last character was the beginning of an element,
			// output a new line and indent the next line
			if ( ',' === $char || '{' === $char || '[' === $char ) {
				$result .= $newLine;
				if ( '{' === $char || '[' === $char ) {
					$pos ++;
				}
				for ( $j = 0; $j < $pos; $j ++ ) {
					$result .= $indentStr;
				}
			}
		}

		return $result;
	}
}

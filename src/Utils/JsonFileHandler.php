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

		if ( version_compare( PHP_VERSION, '5.4') >= 0) {
			$contents = json_encode( $this->decodedContents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		} else {
			$contents = str_replace( '\/', '/', $this->json_readable_encode( $this->decodedContents ) );
		}


		return (bool) file_put_contents( $this->file, $contents );
	}


/*
    json readable encode
    basically, encode an array (or object) as a json string, but with indentation
    so that i can be easily edited and read by a human

    THIS REQUIRES PHP 5.3+

    Copyleft (C) 2008-2011 BohwaZ <http://bohwaz.net/>

    Licensed under the GNU AGPLv3
*/

function json_readable_encode($in, $indent = 0, Closure $_escape = null)
{
    if (__CLASS__ && isset($this))
    {
        $_myself = array($this, __FUNCTION__);
    }
    elseif (__CLASS__)
    {
        $_myself = array('self', __FUNCTION__);
    }
    else
    {
        $_myself = __FUNCTION__;
    }

    if (is_null($_escape))
    {
        $_escape = function ($str)
        {
            return str_replace(
                array('\\', '"', "\n", "\r", "\b", "\f", "\t", '/', '\\\\u'),
                array('\\\\', '\\"', "\\n", "\\r", "\\b", "\\f", "\\t", '\\/', '\\u'),
                $str);
        };
    }

    $out = '';

    foreach ($in as $key=>$value)
    {
        $out .= str_repeat("\t", $indent + 1);
        $out .= "\"".$_escape((string)$key)."\": ";

        if (is_object($value) || is_array($value))
        {
            $out .= "\n";
            $out .= call_user_func($_myself, $value, $indent + 1, $_escape);
        }
        elseif (is_bool($value))
        {
            $out .= $value ? 'true' : 'false';
        }
        elseif (is_null($value))
        {
            $out .= 'null';
        }
        elseif (is_string($value))
        {
            $out .= "\"" . $_escape($value) ."\"";
        }
        else
        {
            $out .= $value;
        }

        $out .= ",\n";
    }

    if (!empty($out))
    {
        $out = substr($out, 0, -2);
    }

    $out = str_repeat("\t", $indent) . "{\n" . $out;
    $out .= "\n" . str_repeat("\t", $indent) . "}";

    return $out;
}
}

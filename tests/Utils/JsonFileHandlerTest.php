<?php

namespace tad\WPCLI\Utils;


use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use tad\WPCLI\Exceptions\FileBadFormatException;
use tad\WPCLI\Exceptions\FileContentsException;

class JsonFileHandlerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var vfsStreamDirectory
	 */
	protected $root;

	public function setUp() {
		$this->root = vfsStream::setup( 'root' );
	}

	/**
	 * @test
	 * it should throw if file is empty
	 */
	public function it_should_throw_if_file_is_empty() {
		$file = new vfsStreamFile( 'some.json' );
		$file->setContent( '' );
		$this->root->addChild( $file );

		$this->expectException( FileContentsException::class );

		$sut = $this->make_instance();
		$sut->setFile( $this->root->url() . '/some.json' )->readFileContents();
	}

	/**
	 * @return JsonFileHandler
	 */
	private function make_instance() {
		return new JsonFileHandler();
	}

	/**
	 * @test
	 * it should throw if file does not contain valid json data
	 */
	public function it_should_throw_if_file_does_not_contain_valid_json_data() {
		$file = new vfsStreamFile( 'some.json' );
		$file->setContent( 'foo' );
		$this->root->addChild( $file );

		$this->expectException( FileBadFormatException::class );

		$sut = $this->make_instance();
		$sut->setFile( $this->root->url() . '/some.json' )->readFileContents();
	}

	/**
	 * @test
	 * it should add property if missing when adding value to array property
	 */
	public function it_should_add_property_if_missing_when_adding_value_to_array_property() {
		$file            = new vfsStreamFile( 'some.json' );
		$existing        = new \stdClass();
		$existing->prop1 = 'val1';
		$file->setContent( json_encode( $existing ) );
		$this->root->addChild( $file );

		$sut      = $this->make_instance();
		$filePath = $this->root->url() . '/some.json';
		$sut->setFile( $filePath )->readFileContents();
		$sut->addPropertyValue( 'prop2', 'foo', 'bar' )->write();

		$expected        = new \stdClass();
		$expected->prop1 = 'val1';
		$expected->prop2 = [ 'foo' => 'bar' ];
		$expected        = json_encode( $expected );
		$this->assertJson( $file->getContent() );
		$this->assertJsonStringEqualsJsonFile( $filePath, $expected );
	}

	/**
	 * @test
	 * it should merge array property when adding value key pair to existing array property
	 */
	public function it_should_merge_array_property_when_adding_value_key_pair_to_existing_array_property() {
		$file     = new vfsStreamFile( 'some.json' );
		$existing = <<<JSON
{
	"prop1": "val1",
	"prop2": {
		"bar": "baz"
	}
}
JSON;
		$file->setContent( $existing );
		$this->root->addChild( $file );

		$sut      = $this->make_instance();
		$filePath = $this->root->url() . '/some.json';
		$sut->setFile( $filePath )->readFileContents();
		$sut->addPropertyValue( 'prop2', 'foo', 'bar' )->write();

		$expected = <<<JSON
{
	"prop1": "val1",
	"prop2": {
		"bar": "baz",
		"foo": "bar"
	}
}
JSON;
		$expected = $expected;
		$this->assertJson( $file->getContent() );
		$this->assertJsonStringEqualsJsonFile( $filePath, $expected );
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( JsonFileHandler::class, $sut );
	}
}

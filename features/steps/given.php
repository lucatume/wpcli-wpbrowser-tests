<?php

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use WP_CLI\Process;

$steps->Given(
	'/^an empty directory$/', function ( $world ) {
	$world->create_run_dir();
}
);

$steps->Given(
	'/^an empty cache/', function ( $world ) {
	$world->variables['SUITE_CACHE_DIR'] = FeatureContext::create_cache_dir();
}
);

$steps->Given(
	'/^an? ([^\s]+) file:$/', function ( $world, $path, PyStringNode $content ) {
	$content   = (string) $content . "\n";
	$full_path = $world->variables['RUN_DIR'] . "/$path";
	Process::create( \WP_CLI\utils\esc_cmd( 'mkdir -p %s', dirname( $full_path ) ) )
	       ->run_check();
	file_put_contents( $full_path, $content );
}
);

$steps->Given(
	'/^WP files$/', function ( $world ) {
	$world->download_wp();
}
);

$steps->Given(
	'/^wp-config\.php$/', function ( $world ) {
	$world->create_config();
}
);

$steps->Given(
	'/^a database$/', function ( $world ) {
	$world->create_db();
}
);

$steps->Given(
	'/^a WP install$/', function ( $world ) {
	$world->install_wp();
}
);

$steps->Given(
	"/^a WP install in '([^\s]+)'$/", function ( $world, $subdir ) {
	$world->install_wp( $subdir );
}
);

$steps->Given(
	'/^a WP multisite (subdirectory|subdomain)?\s?install$/', function ( $world, $type = 'subdirectory' ) {
	$world->install_wp();
	$subdomains = ! empty( $type ) && 'subdomain' === $type ? 1 : 0;
	$world->proc( 'wp core install-network', array( 'title' => 'WP CLI Network', 'subdomains' => $subdomains ) )
	      ->run_check();
}
);

$steps->Given(
	'/^these installed and active plugins:$/', function ( $world, $stream ) {
	$plugins = implode( ' ', array_map( 'trim', explode( PHP_EOL, (string) $stream ) ) );
	$world->proc( "wp plugin install $plugins --activate" )
	      ->run_check();
}
);

$steps->Given(
	'/^a custom wp-content directory$/', function ( $world ) {
	$wp_config_path = $world->variables['RUN_DIR'] . "/wp-config.php";

	$wp_config_code = file_get_contents( $wp_config_path );

	$world->move_files( 'wp-content', 'my-content' );
	$world->add_line_to_wp_config( $wp_config_code, "define( 'WP_CONTENT_DIR', dirname(__FILE__) . '/my-content' );" );

	$world->move_files( 'my-content/plugins', 'my-plugins' );
	$world->add_line_to_wp_config( $wp_config_code, "define( 'WP_PLUGIN_DIR', __DIR__ . '/my-plugins' );" );

	file_put_contents( $wp_config_path, $wp_config_code );
}
);

$steps->Given(
	'/^download:$/', function ( $world, TableNode $table ) {
	foreach ( $table->getHash() as $row ) {
		$path = $world->replace_variables( $row['path'] );
		if ( file_exists( $path ) ) {
			// assume it's the same file and skip re-download
			continue;
		}

		Process::create( \WP_CLI\Utils\esc_cmd( 'curl -sSL %s > %s', $row['url'], $path ) )
		       ->run_check();
	}
}
);

$steps->Given(
	'/^save (STDOUT|STDERR) ([\'].+[^\'])?as \{(\w+)\}$/', function ( $world, $stream, $output_filter, $key ) {

	$stream = strtolower( $stream );

	if ( $output_filter ) {
		$output_filter = '/' . trim( str_replace( '%s', '(.+[^\b])', $output_filter ), "' " ) . '/';
		if ( false !== preg_match( $output_filter, $world->result->$stream, $matches ) ) {
			$output = array_pop( $matches );
		} else {
			$output = '';
		}
	} else {
		$output = $world->result->$stream;
	}
	$world->variables[ $key ] = trim( $output, "\n" );
}
);

$steps->Given(
	'/^a new Phar(?: with version "([^"]+)")$/', function ( $world, $version ) {
	$world->build_phar( $version );
}
);

$steps->Given(
	'/^save the (.+) file ([\'].+[^\'])?as \{(\w+)\}$/', function ( $world, $filepath, $output_filter, $key ) {
	$full_file = file_get_contents( $world->replace_variables( $filepath ) );

	if ( $output_filter ) {
		$output_filter = '/' . trim( str_replace( '%s', '(.+[^\b])', $output_filter ), "' " ) . '/';
		if ( false !== preg_match( $output_filter, $full_file, $matches ) ) {
			$output = array_pop( $matches );
		} else {
			$output = '';
		}
	} else {
		$output = $full_file;
	}
	$world->variables[ $key ] = trim( $output, "\n" );
}
);

$steps->Given(
	'/^a misconfigured WP_CONTENT_DIR constant directory$/', function ( $world ) {
	$wp_config_path = $world->variables['RUN_DIR'] . "/wp-config.php";

	$wp_config_code = file_get_contents( $wp_config_path );

	$world->add_line_to_wp_config( $wp_config_code, "define( 'WP_CONTENT_DIR', '' );" );

	file_put_contents( $wp_config_path, $wp_config_code );
}
);

$steps->Given(
	'/^the next command is called with the `(.+)` (parameter|option)$/', function ( $world, $parameter, $type ) {
	if ( $type === 'option' ) {
		if ( ! empty( $world->variables['appendParameter'] ) ) {
			$world->variables['appendParameter'] .= ' ' . $parameter;
		} else {
			$world->variables['appendParameter'] = ' ' . $parameter;
		}

		return;
	}

	$world->variables['parameterName'] = $parameter;
}
);

$steps->Given(
	'/^the value of the parameter is `(.+)`( from data)*$/', function ( $world, $value, $fromData = null ) {
	if ( empty( $world->variables['parameterName'] ) ) {
		throw new \Behat\Behat\Exception\UndefinedException( 'Parameter value is missing' );
	}

	if ( ! empty( $fromData ) ) {
		$value = $world->get_data_dir( $value );

		if ( ! file_exists( $value ) ) {
			throw new \Behat\Behat\Exception\ErrorException( 0, "File '{$value}' does not exist.", __FILE__, __LINE__ - 3 );
		}
	}

	$toAppend = ' ' . $world->variables['parameterName'] . '="' . $value . '"';

	if ( ! empty( $world->variables['appendParameter'] ) ) {
		$world->variables['appendParameter'] .= $toAppend;
	} else {
		$world->variables['appendParameter'] = $toAppend;
	}

	$world->variables['parameterName'] = null;
}
);

$steps->Given(
	'/^the global \$PATH var includes the data dir$/', function ( $world ) {
	$path    = getenv( 'PATH' );
	$dataDir = $world->get_data_dir();
	$newPath = empty( $path ) ? $dataDir : $dataDir . ':' . $path;

	putenv( 'PATH=' . $newPath );
}
);

$steps->Given(
	'/^the `(.+)` data folder contains the `(.+)` file with contents:$/', function (
	$world,
	$folder,
	$file,
	$contents
) {
	$path = $world->get_data_dir( $folder );

	if ( ! is_dir( $path ) ) {
		throw new \Behat\Behat\Exception\ErrorException( 0, "Folder '{$path}' does not exist.", __FILE__, __LINE__ - 3 );
	}

	$filePath = rtrim( $path, '/' ) . '/' . trim( $file, '/' );

	file_put_contents( $filePath, $world->replace_variables( (string) $contents ) );
}
);

$steps->Given(
	'/^the \'([^\']*)\' (plugin|theme) folder exists$/', function ( $world, $folder, $type ) {
	if ( $type === 'plugin' ) {
		$rootDir = $world->variables['RUN_DIR'] . '/wp-content/plugins';
		mkdir( $rootDir . '/' . ltrim( $folder, '/' ), 0777, true );

		if ( ! is_dir( $rootDir . '/' . ltrim( $folder, '/' ) ) ) {
			throw new Exception( "Could not create '{$folder}' plugin folder" );
		}
	} else {
		$rootDir = $world->variables['RUN_DIR'] . '/wp-content/themes';
		mkdir( $rootDir . '/' . ltrim( $folder, '/' ), 0777, true );

		if ( ! is_dir( $rootDir . '/' . ltrim( $folder, '/' ) ) ) {
			throw new Exception( "Could not create '{$folder}' theme folder" );
		}
	}
}
);

$steps->Given(
	'/^the \'([^\']*)\' (plugin|theme) folder does not exist$/', function ( $world, $type, $folder ) {
	if ( $type === 'plugin' ) {
		$rootDir = $world->variables['RUN_DIR'] . '/wp-content/plugins';
		recursiveRmdir( $rootDir . '/' . ltrim( $folder, '/' ) );

		if ( is_dir( $rootDir . '/' . ltrim( $folder, '/' ) ) ) {
			throw new Exception( "Could not remove '{$folder}' plugin folder" );
		}
	} else {
		$rootDir = $world->variables['RUN_DIR'] . '/wp-content/themes';
		recursiveRmdir( $rootDir . '/' . ltrim( $folder, '/' ) );

		if ( is_dir( $rootDir . '/' . ltrim( $folder, '/' ) ) ) {
			throw new Exception( "Could not remove '{$folder}' theme folder" );
		}
	}
}
);

$steps->Given(
	'/^the file \'([^\']*)\' in the \'([^\']*)\' plugin contains:$/', function ( $world, $file, $slug, $contents ) {
	$rootDir = $world->variables['RUN_DIR'] . '/wp-content/plugins/' . $slug;

	if ( ! is_dir( $rootDir ) ) {
		throw new Exception( "Plugin folder '{$slug}' does not exist." );
	}

	$contents = $world->replace_variables( (string) $contents );
	$put      = file_put_contents( $rootDir . '/' . ltrim( $file, '/' ), $contents );

	if ( false === $put ) {
		throw new Exception( "Could not put file '{$file}' in '{$slug}' plugin folder." );
	}
}
);

$steps->Given(
	'/^the value of the parameter is the \'([^\']*)\' plugin folder path$/', function ( $world, $slug ) {
	$rootDir = $world->variables['RUN_DIR'] . '/wp-content/plugins/' . $slug;

	$toAppend = ' ' . $world->variables['parameterName'] . '="' . $rootDir . '"';

	if ( ! empty( $world->variables['appendParameter'] ) ) {
		$world->variables['appendParameter'] .= $toAppend;
	} else {
		$world->variables['appendParameter'] = $toAppend;
	}

	$world->variables['parameterName'] = null;
}
);


$steps->Given(
	'/^I will answer \'([^\']*)\' to the \'([^\']*)\' question$/', function ( $world, $answer ) {
	// question is not used; it has merely a representative function
	if ( ! isset( $world->variables['input'] ) ) {
		$world->variables['input'] = array();
	}

	/** @var FeatureContext $world */
	$world->variables['input'][] = $answer;
}
);

$steps->Given(
	'/^I run `composer ([^`]*)` in the \'([^\']*)\' (plugin|theme)$/', function ( $world, $composerCommand, $slug, $type ) {
	if ( $type === 'plugin' ) {
		$subdir = '/wp-content/plugins/' . $slug;
	} else {
		$subdir = '/wp-content/themes/' . $slug;
	}

	$dir = $world->variables['RUN_DIR'] . $subdir;

	if ( ! is_dir( $dir ) ) {
		mkdir( $dir, 0777, true );
	}

	$cmd = 'composer ' . $composerCommand . ' -d=' . $dir;

	$world->result = invoke_proc( $world->proc( $cmd, array() ), 'run' );
}
);

$steps->Given(
	'/^I\'m working on the \'([^\']*)\' (plugin|theme)$/', function ( $world, $slug, $type ) {
	if ( $type === 'plugin' ) {
		$subdir = '/wp-content/plugins/' . $slug;
	} else {
		$subdir = '/wp-content/themes/' . $slug;
	}
	$dir = $world->variables['RUN_DIR'] . $subdir;

	$world->variables['cwd'] = $dir;
}
);

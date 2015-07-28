<?php

namespace Jacere\Bramble;

use Jacere\Bramble\Core\Autoloader;
use Jacere\Bramble\Core\Application;

define('BRAMBLE_DIR', str_replace('\\', '/', __DIR__));
define('BRAMBLE_NS', __NAMESPACE__);
define('BRAMBLE_BASE', '/');
define('BRAMBLE_TEMPLATES', BRAMBLE_DIR.'/.templates');

define('BRAMBLE_AUTOLOAD', 'custom');

ob_start();

if (BRAMBLE_AUTOLOAD === 'composer') {
	require(BRAMBLE_DIR.'/vendor/autoload.php');
}
else if (BRAMBLE_AUTOLOAD === 'custom') {
	require(BRAMBLE_DIR.'/vendor/jacere/composer-autoload/autoload.php');
}
else if (BRAMBLE_AUTOLOAD === 'internal') {
	require(BRAMBLE_DIR . '/Core/Autoloader.php');
	Autoloader::register(__NAMESPACE__, BRAMBLE_DIR);
	Autoloader::register('Jacere\Skhema', BRAMBLE_DIR.'/vendor/jacere/skhema');
	Autoloader::register('Jacere\Subvert', BRAMBLE_DIR.'/vendor/jacere/subvert');
	Autoloader::register('Spyc', BRAMBLE_DIR.'/vendor/mustangostang/spyc/Spyc.php');
}

Application::cache('PhpFileCache', ['path' => realpath(BRAMBLE_DIR.'/.cache')]);
Application::start();

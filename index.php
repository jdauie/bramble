<?php

namespace Jacere\Bramble;

use Jacere\Bramble\Core\Autoloader;
use Jacere\Bramble\Core\Application;

define('BRAMBLE_DIR', str_replace('\\', '/', __DIR__));
define('BRAMBLE_NS', __NAMESPACE__);
define('BRAMBLE_BASE', '/');
define('BRAMBLE_TEMPLATES', BRAMBLE_DIR.'/.templates');

ob_start();

require_once(BRAMBLE_DIR.'/Core/Autoloader.php');

Autoloader::register(__NAMESPACE__, BRAMBLE_DIR);
Autoloader::register('Jacere\Skhema', realpath(BRAMBLE_DIR.'/../jacere/skhema'));
Autoloader::register('Jacere\Subvert', realpath(BRAMBLE_DIR.'/../subvert'));
Autoloader::register('Spyc', BRAMBLE_DIR.'/lib/spyc/Spyc.php');

Application::cache('PhpFileCache', ['path' => realpath(BRAMBLE_DIR.'/.cache')]);
Application::start();

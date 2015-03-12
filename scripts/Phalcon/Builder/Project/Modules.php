<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Developer Tools                                                |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2014 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Builder\Project;

use Phalcon\Builder\Controller;
use Phalcon\DI;
use Phalcon\Web\Tools;

/**
 * Multi-Module
 *
 * Builder to create multi-module application skeletons
 *
 * @category    Phalcon
 * @package     Scripts
 * @copyright   Copyright (c) 2011-2014 Phalcon Team (team@phalconphp.com)
 * @license     New BSD License
 */
class Modules extends ProjectBuilder
{

    private $_dirs = array(
        'apps/',
        'config/',
        'public',
        'public/img',
        'public/css',
        'public/temp',
        'public/files',
        'public/js',
        '.phalcon'
    );

    private $_moduleDirTemplate = array(
        '/',
        '/views',
        '/config',
        '/models',
        '/controllers',
        '/views/index',
        '/views/layouts',
    );
    private $_moduleDir=array();
    private $_modules = [];

    /**
     * Create indexController file
     *
     * @param $path
     * @param $name
     */
    private function createControllerFile($path, $name)
    {
        foreach ($this->_modules as $module) {
            $modelBuilder = new Controller(
                array(
                    'module' => $module,
                    'name' => 'index',
                    'controllersDir' => $path . "apps/$module/controllers/",
                    'namespace' => ucfirst($name) . '\\' . ucfirst($module) . '\\' . 'Controllers',
                    'baseClass' => 'ControllerBase'
                )
            );
            $modelBuilder->build();
        }
    }

    /**
     * Create .htaccess files by default of application
     * @param $path
     * @param $templatePath
     */
    private function createHtaccessFiles($path, $templatePath)
    {

        if (file_exists($path . '.htaccess') == false) {
            $code = '<IfModule mod_rewrite.c>' . PHP_EOL .
                "\t" . 'RewriteEngine on' . PHP_EOL .
                "\t" . 'RewriteRule  ^$ public/    [L]' . PHP_EOL .
                "\t" . 'RewriteRule  (.*) public/$1 [L]' . PHP_EOL .
                '</IfModule>';
            file_put_contents($path . '.htaccess', $code);
        }

        if (file_exists($path . 'public/.htaccess') == false) {
            file_put_contents($path . 'public/.htaccess', file_get_contents($templatePath . '/project/modules/htaccess'));
        }

        if (file_exists($path . 'index.html') == false) {
            $code = '<html><body><h1>Mod-Rewrite is not enabled</h1><p>Please enable rewrite module on your web server to continue</body></html>';
            file_put_contents($path . 'index.html', $code);
        }
    }

    /**
     * Create view files by default
     * @param $path
     * @param $templatePath
     */
    private function createIndexViewFiles($path, $templatePath)
    {
        $getFile = $templatePath . '/project/modules/views/index.phtml';
        foreach ($this->_modules as $module) {
            $putFile = $path . "apps/$module/views/index.phtml";
            $this->generateFile($getFile, $putFile);
        }

        $getFile = $templatePath . '/project/modules/views/index/index.phtml';
        foreach ($this->_modules as $module) {
            $putFile = $path . "apps/$module/views/index/index.phtml";
            $this->generateFile($getFile, $putFile);
        }
    }

    /**
     * Creates the configuration
     *
     * @param $path
     * @param $templatePath
     * @param $name
     * @param $type
     */
    private function createConfig($path, $templatePath, $name, $type)
    {
        $getFile = $templatePath . '/project/modules/config.' . $type;
        foreach ($this->_modules as $module) {
            $putFile = $path . "apps/$module/config/config." . $type;
            $this->generateFile($getFile, $putFile, $name);
        }
    }

    /**
     * Create ControllerBase
     *
     * @param $path
     * @param $templatePath
     * @param $name
     */
    private function createControllerBase($path, $templatePath, $name)
    {
        $getFile = $templatePath . '/project/modules/ControllerBase.php';
        foreach ($this->_modules as $module) {
            $putFile = $path . "apps/$module/controllers/ControllerBase.php";
            $this->generateFile($getFile, $putFile, $name, $module);
        }
    }

    /**
     * Create ControllerBase
     *
     * @param $path
     * @param $templatePath
     * @param $name
     */
    private function createModule($path, $templatePath, $name)
    {
        $getFile = $templatePath . '/project/modules/Module.php';
        foreach ($this->_modules as $module) {
            $putFile = $path . "apps/$module/Module.php";
            $this->generateFile($getFile, $putFile, $name, $module);
        }
    }

    /**
     * Create Bootstrap file by default of application
     *
     * @param $name
     * @param $path
     * @param $templatePath
     */
    private function createBootstrapFile($name, $path, $templatePath)
    {
        $getFile = $templatePath . '/project/modules/index.php';
        $putFile = $path . 'public/index.php';
        $this->generateFile($getFile, $putFile, $name);

        $getFile = $templatePath . '/project/modules/services.php';
        $putFile = $path . 'config/services.php';
        $this->generateServiceFile($getFile, $putFile, $name);

        $getFile = $templatePath . '/project/modules/modules.php';
        $putFile = $path . 'config/modules.php';
        $this->generateProjectModuleFile($getFile, $putFile, $name);

        $getFile = $templatePath . '/project/modules/loader.php';
        $putFile = $path . 'config/loader.php';
        $this->generateProjectLoaderFile($getFile, $putFile, $name);
    }

    /**
     * Build project
     *
     * @param $name
     * @param $path
     * @param $templatePath
     * @param $options
     *
     * @return bool
     */
    public function build($name, $path, $templatePath, $options)
    {
        $modules = isset($options['modules'])?explode(',',$options['modules']):array('frontend');
        $this->buildDirectories($this->_dirs,$path);
        foreach ($modules as $module) {
            $prefix = 'apps' . DIRECTORY_SEPARATOR . "$module";
            $this->_moduleDir[$module] = array_map(function($item) use ($prefix){
                return $prefix . $item;
            },$this->_moduleDirTemplate);
            $this->buildDirectories($this->_moduleDir[$module], $path);
            $this->_modules[] = $module;
        }

        $this->getVariableValues($options);

        if (isset($options['useConfigIni']) && $options['useConfigIni']) {
            $this->createConfig($path, $templatePath, $name, 'ini');
        } else {
            $this->createConfig($path, $templatePath, $name, 'php');
        }

        $this->createBootstrapFile($name, $path, $templatePath);
        $this->createHtaccessFiles($path, $templatePath);
        $this->createControllerBase($path, $templatePath, $name);
        $this->createModule($path, $templatePath, $name);
        $this->createIndexViewFiles($path, $templatePath);
        $this->createControllerFile($path, $name);

        if ($options['enableWebTools']) {
            Tools::install($path);
        }

        return true;
    }
    const ModuleTemplate = <<< MEOF
    '@@module_path@@' => array(
        'className' => '@@namespace@@\@@module@@\Module',
        'path' => __DIR__ . '/../apps/@@module_path@@/Module.php'
    ),

MEOF;

    private function generateProjectModuleFile($getFile, $putFile, $name)
    {
        if (file_exists($putFile) == false) {
            $raw = file_get_contents($getFile);
            $content = '';
            foreach ($this->_modules as $module) {
                $str = preg_replace('/@@name@@/', $name, self::ModuleTemplate);
                $str = preg_replace('/@@namespace@@/', ucfirst($name), $str);
                $str = preg_replace('/@@module@@/', ucfirst($module), $str);
                $str = preg_replace('/@@module_path@@/', $module, $str);
                $content .= $str;
            }

            $output = preg_replace('/@@modules@@/', $content, $raw);
            file_put_contents($putFile, $output);
        }
    }

    const         RouteTemplate = <<< REOF

    \$router->add('/@@module_path@@/:controller/:action/:params', [
        'namespace' => 'Test\@@module@@\Controllers',
        'module' => '@@module_path@@',
        'controller' => 1,
        'action' => 2,
        'params' => 3,
    ]);

REOF;

    const AnnotationRouteTemplate = <<< AEOF

    \$router->addModuleResource('@@module_path@@', '@@namespace@@\@@module@@\Controllers\Index', '/@@module_path@@/index');

AEOF;



    private function generateServiceFile($getFile, $putFile, $name)
    {

        if (file_exists($putFile) == false) {
            $raw = file_get_contents($getFile);
            $content = '';
            foreach ($this->_modules as $module) {
                $str = preg_replace('/@@module_path@@/', $module, self::AnnotationRouteTemplate);
                $str = preg_replace('/@@module@@/', ucfirst($module), $str);
                $str = preg_replace('/@@namespace@@/', ucfirst($name), $str);
                $content .= $str;
            }
            $output = preg_replace('/@@module_path@@/', $this->_modules[0], $raw);
            $output = preg_replace('/@@module@@/', ucfirst($this->_modules[0]), $output);
            $output = preg_replace('/@@module_route@@/', $content, $output);
            $output = preg_replace('/@@name@@/', $name, $output);
            $output = preg_replace('/@@namespace@@/', ucfirst($name), $output);
            file_put_contents($putFile, $output);
        }
    }


    const RegisterTemplate = <<< REG
        '@@namespace@@\@@module@@\Controllers' => __DIR__ . '/../apps/@@module_path@@/controllers/',
        '@@namespace@@\@@module@@\Models'      => __DIR__ . '/../apps/@@module_path@@//models/',

REG;

    private function generateProjectLoaderFile($getFile, $putFile, $name)
    {
        if (file_exists($putFile) == false) {
            $raw = file_get_contents($getFile);
            $content = '';
            foreach ($this->_modules as $module) {
                $str = preg_replace('/@@module_path@@/', $module, self::RegisterTemplate);
                $str = preg_replace('/@@module@@/', ucfirst($module), $str);
                $str = preg_replace('/@@namespace@@/', ucfirst($name), $str);
                $content .= $str;
            }
            $output = preg_replace('/@@register@@/', $content, $raw);
            file_put_contents($putFile, $output);
        }
    }

}

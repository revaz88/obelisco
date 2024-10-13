<?php

namespace Novosga\Service;

use Exception;
use Novosga\Model\Modulo;
use Novosga\Model\Util\ModuleManifest;
use Novosga\Util\FileUtils;
use ZipArchive;

/**
 * ModuloService.
 *
 * 
 */
class ModuloService extends ModelService
{
    /**
     * @param string $moduleDir
     * @param string $key       Module keyname
     * @param bool   $status    Default module status
     *
     * @return Modulo
     */
    public function install($moduleDir, $key, $status = 0)
    {
        $this->verifyKey($key);
        $this->verifyDir($moduleDir);
        $manifest = $this->parseManifest($moduleDir, $key);
        $module = $manifest->getModule();
        $module->setStatus($status);

        $this->invokeScripts($manifest, 'pre-install');

        $this->em->persist($module);
        $this->em->flush();

        $this->invokeScripts($manifest, 'post-install');

        return $module;
    }

    /**
     * @param string $zipname
     *
     * @return Modulo
     */
    public function extractAndInstall($zipname)
    {
        $moduleDir = $this->extract($zipname);
        $name = str_replace('.zip', '', basename($zipname));

        return $this->install($moduleDir, $name);
    }

    /**
     * @param string $key
     *
     * @throws Exception
     */
    public function uninstall($key)
    {
        $module = $this->em->createQuery('SELECT e FROM Novosga\Model\Modulo e WHERE e.chave = :key')
                ->setParameter('key', $key)
                ->getOneOrNullResult();
        if (!$module) {
            throw new Exception(sprintf(_('Módulo %s no instalado'), $key));
        }
        $this->em->remove($module);
        $this->em->flush();

        $manifest = $this->parseManifest($module->getRealPath(), $module->getChave());
        $this->invokeScripts($manifest, 'post-remove');

        FileUtils::rmdir($module->getRealPath());
    }

    /**
     * @param string $zipname
     *
     * @return string The module dir
     *
     * @throws Exception
     */
    public function extract($zipname)
    {
        $name = str_replace('.zip', '', basename($zipname));
        $path = $this->verifyKey($name);

        $dir = MODULES_PATH.DS.$path[0];
        $moduleDir = $dir.DS.$path[1];

        if (!file_exists($zipname)) {
            throw new Exception(sprintf(_('Archivo no encontrado: %s'), $zipname));
        }

        if (file_exists($moduleDir)) {
            throw new Exception(_('No se puede crear el módulo de directorio'));
        }

        $cacheDir = NOVOSGA_CACHE.DS.$name;

        // zip extract
        $zip = new ZipArchive();
        $zip->open($zipname);
        $zip->extractTo($cacheDir);
        $zip->close();

        // vendor dir
        if (!is_dir($dir)) {
            if (!@mkdir($dir)) {
                FileUtils::rm(NOVOSGA_CACHE.DS.$name);
                throw new Exception(_('No fue posible crear el directorio del módulo'));
            }
            chmod($dir, 0777);
        }

        if (!@rename($cacheDir, $moduleDir)) {
            FileUtils::rm(NOVOSGA_CACHE.DS.$name);
            throw new Exception(_('No fue posible mover los archivos al directorio de los módulos'));
        }
        FileUtils::rm(NOVOSGA_CACHE.DS.$name);

        return $moduleDir;
    }

    /**
     * Verifica se o módulo possui os arquivos necessários.
     *
     * @param string $moduleDir
     *
     * @throws Exception
     */
    public function verifyDir($moduleDir)
    {
        $moduleName = basename($moduleDir);
        // module structure
        $files = array(
            'manifest.json',
            ucfirst($moduleName).'Controller.php',
            'public'.DS.'images'.DS.'icon.png',
            'public'.DS.'css'.DS.'style.css',
            'public'.DS.'js'.DS.'script.js',
            'views'.DS.'index.html.twig',
        );
        foreach ($files as $file) {
            if (!file_exists($moduleDir.DS.$file)) {
                throw new Exception(sprintf(_('Archivo %s no encontrado'), $file));
            }
        }
    }

    /**
     * @param string $key {vendorName}.{moduleName}
     *
     * @return array 0 => {vendorName}, 1 => {moduleName}
     *
     * @throws Exception
     */
    public function verifyKey($key)
    {
        $path = explode('.', $key);
        if (sizeof($path) !== 2) {
            throw new Exception(sprintf(_('Formato no válido en el nombre del módulo: %s. Era esperado {vendorName}.{moduleName}'), $key));
        }

        return $path;
    }

    /**
     * @param type $moduleDir
     * @param type $key
     *
     * @return ModuleManifest
     *
     * @throws Exception
     */
    public function parseManifest($moduleDir, $key)
    {
        $data = file_get_contents($moduleDir.DS.'manifest.json');
        $json = json_decode($data, true);
        if (!$json) {
            throw new Exception(_('El manifiesto no contine un archivo JSON válido.'));
        }

        return new ModuleManifest($key, $json);
    }

    private function invokeScripts(ModuleManifest $manifest, $name)
    {
        $scripts = $manifest->getScript($name);
        if (is_array($scripts) && sizeof($scripts)) {
            foreach ($scripts as $script) {
                $tokens = explode('::', $script);
                if (sizeof($tokens) !== 2) {
                    throw new Exception(_('Formato del nombre de la secuencia de comandos no válida. Era experado <ClassName>::<methodName>.'));
                }
                $namespace = 'modules\\'.implode('\\', explode('.', $manifest->getModule()->getChave()));
                $className = "$namespace\\".ucfirst($tokens[0]);

                $obj = new $className();
                $method = new \ReflectionMethod($obj, $tokens[1]);
                $method->invokeArgs($obj, array($this->em));
            }
        }
    }
}

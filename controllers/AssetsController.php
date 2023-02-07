<?php

class Klear_AssetsController extends Zend_Controller_Action
{
    protected $_defaultHeaders;
    protected $_applyStrongCache;

    /**
     * @var Klear_Model_SiteConfig
     */
    protected $_siteConfig;

    protected $_allowedFileTypes = array(
        "js" => 'application/x-javascript',
        "css" => 'text/css',
        "html" => 'text/html',
        "htm" => 'text/html',
        "woff" => 'application/x-font-woff',
        "eot" => 'application/vnd.ms-fontobject',
        "svg" => 'image/svg+xml',
        "ttf" => 'application/x-font-ttf'
    );

    public function init()
    {
        $this->_applyStrongCache = ("production" === APPLICATION_ENV);
        $this->_applyStrongCache = true;

        $this->_helper->layout->disableLayout();
        $this->_helper->getHelper('viewRenderer')->setNoRender();

        $this->_defaultHeaders = array(
            'Pragma' => 'public',
            'Cache-control' => 'maxage=' . 10, // ~1 minute (e-tag + Last-Modified header are still working!
            'Expires' => gmdate('D, d M Y H:i:s', (time() + 10)) . ' GMT'
        );

        $this->_siteConfig = $this->getInvokeArg('bootstrap')
            ->getResource('modules')
            ->offsetGet('klear')
            ->getOption('siteConfig');
    }

    protected function _buildPath($base)
    {
        $front = $this->getFrontController();
        $moduleDirectory = $front->getModuleDirectory($this->getRequest()->getParam('moduleName'));
        return $moduleDirectory . $base . $this->getRequest()->getParam("file");
    }

    protected function _getFileExtension($file)
    {
        $pathInfo = pathinfo((string) $file);
        return $pathInfo['extension'];
    }

    protected function _returnFile($file)
    {
        /*
         * Dejamos pasar a las imágenes de las librerías externas.
         * Cabeceras de ficheros CSS JS según extensión
         */
        if (file_exists($file)) {

            if (str_contains(mime_content_type($file), 'image')) {
                return $this->_sendImage($file);
            } else {

                $fileType = $this->_getFileExtension($file);
                if (!isset($this->_allowedFileTypes[strtolower((string) $fileType)])) {
                    throw new \Klear_Exception_Default($this->_helper->translate('Forbidden file type'));
                }

                $this->_compress(
                    $file,
                    $fileType
                );
            }
        }
    }

    public function jsAction()
    {
        $jsFile = $this->_buildPath('/assets/js/');
        $this->_returnFile($jsFile);
    }

    public function cssExtendedAction()
    {
        $file = null;
        $cssExtendedConfig = $this->_siteConfig->getCssExtendedConfig();

        if ($cssExtendedConfig) {
            $pluginClass = "Klear_Model_Css_";
            $pluginName = $this->getRequest()->getParam('plugin');
            $pluginParts = explode('-', (string) $pluginName);

            foreach ($pluginParts as $part) {
                $pluginClass .= ucfirst($part);
            }

            if (!class_exists($pluginClass)) {
                throw new Klear_Exception_Default('No Css class found');
            }

            $plg = new $pluginClass($cssExtendedConfig);
            $fileParam = $this->getRequest()->getParam('file');
            switch ($this->_getFileExtension($fileParam)) {
                case 'css':
                    $file = $plg->getCssFile($fileParam);
                    break;
                case 'png':
                    $file = $plg->getPngFile($fileParam);
                    break;
            }

            $this->_returnFile($file);
        }
    }

    public function themeAction()
    {
        $cssFile = $this->_buildPath('/assets/themes/');
        $this->_returnFile($cssFile);
    }

    public function cssAction()
    {
        $cssFile = $this->_buildPath('/assets/css/');
        $this->_returnFile($cssFile);
    }

    public function binAction()
    {
        $binFile = $this->_buildPath('/assets/bin/');
        $this->_sendRaw($binFile);
    }

    public function cssImageAction()
    {
        $imgFile = $this->_buildPath('/assets/css/');
        return $this->_sendImage($imgFile);
    }

    public function imageAction()
    {
        $imgFile = $this->_buildPath('/assets/images/');
        return $this->_sendImage($imgFile);
    }

    /**
     * TODO: Esto es carne de Action Helper
     * @param string $file
     */
    protected function _sendImage($file)
    {
        // TODO: Cachear propiedades de cada imagen?
        // FIXME: Try not to instanciate Imagick on every request
        $image = new Imagick($file);
        $hash = $image->getImageSignature();

        $response = $this->getResponse();

        if ($this->_hashMatches($hash)) {
            $this->_sendHeaders();
            $response->setHttpResponseCode(304);
            return;
        }

        $format = 'image/' . strtolower($image->getImageFormat());

        $headers = array();
        if ($this->_applyStrongCache) {
            $headers['ETag'] = $hash;
        }
        $headers['Content-type'] = $format;
        $headers['Content-length'] = filesize($file);
        $this->_sendHeaders($headers);

        readFile($file);
    }

    protected function _hashMatches($hash)
    {
        $matchHash = $this->getRequest()->getHeader('If-None-Match');
        if ($matchHash == $hash) {
            return true;
        }
        return false;
    }


    protected function _sendRaw($file)
    {
        $response = $this->getResponse();

        $lastModifiedTime = filemtime($file);
        if ($this->_isUnmodifiedFile($lastModifiedTime)) {
            $response->setHttpResponseCode(304);
            return;
        }

        $headers = array();
        if ($this->_applyStrongCache) {
            $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', $lastModifiedTime) . ' GMT';
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $headers['Content-type'] = $finfo->file($file);
        $headers['Content-length'] = filesize($file);
        $this->_sendHeaders($headers);

        readFile($file);
    }

    protected function _isUnmodifiedFile($lastModifiedTime)
    {
        $modifiedHeader = $this->getRequest()->getHeader('IF-MODIFIED-SINCE');
        if ($modifiedHeader) {
            if ($lastModifiedTime == strtotime((string) $modifiedHeader)) {
                return true;
            }
        }
        return false;
    }

    public function _compress($file, $type)
    {
        $response = $this->getResponse();

        $lastModifiedTime = filemtime($file);
        if ($this->_isUnmodifiedFile($lastModifiedTime)) {
            $response->setHttpResponseCode(304);
            $this->_sendHeaders();
            return;
        }

        $this->getFrontController()->setParam('disableOutputBuffering', true);

        $cache = $this->_getFileCache($file);

        $id = sha1((string) $file);
        $fileContents = $cache->load($id);
        $headers = $cache->load("headers" . $id);

        if ((false === $fileContents) || (false === $headers)) {
            $fileContents = $this->_getContents($file, $type);
            $fileContentType = $this->_allowedFileTypes[strtolower((string) $type)];

            $headers = array();

            if ($this->_applyStrongCache) {
                $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', $lastModifiedTime) . ' GMT';
            }

            $headers['Content-type'] = $fileContentType;
            $headers['Content-length'] = mb_strlen((string) $fileContents);

            $cache->save($fileContents, $id);
            $cache->save($headers, 'headers' . $id);
        }

        $this->_sendHeaders($headers);
        echo $fileContents;
    }

    protected function _getFileCache($file)
    {
        $cacheBackend = 'File';
        if ($this->_siteConfig->assetsCacheDisabled()) {
            $cacheBackend = 'Black-Hole';
        }

        $frontendOptions = array(
            'lifetime' => null,
            'debug_header' => false,
            'automatic_serialization' => true,
            'master_files' => array($file),
            'memorize_headers' => array(
                'content-type',
                'content-length',
                'pragma',
                'cache-control',
                'last-modified'
            ),
            'default_options' => array(
                'cache_with_session_variables' => true,
                'cache_with_cookie_variables' => true,
                'cache_with_post_variables' => true,
                'cache_with_get_variables' => true,
                'make_id_with_session_variables' => false,
                'make_id_with_cookie_variables' => false,
                'make_id_with_post_variables' => false,
                'make_id_with_get_variables' => false
            )
        );

        $backendOptions = array(
            'cache_dir' => APPLICATION_PATH . '/cache/'
        );

        $cache = Zend_Cache::factory(
            'File',
            $cacheBackend,
            $frontendOptions,
            $backendOptions
        );

        return $cache;
    }

    protected function _getContents($file, $type)
    {
        $data = file_get_contents($file);

        if (APPLICATION_ENV !== 'development') {
            if ($this->_siteConfig->minifiersDisabled()) {
                return $data;
            }

            if ($this->_applyStrongCache) {
                switch (strtolower((string) $type)) {
                    case "js":
                        $minifier = new Iron_Minify_JsMin($data);
                        break;
                    case "css":
                        $minifier = new Iron_Minify_CssCompressor($data);
                        break;
                    default:
                        return $data;
                }
                $data = $minifier->min();
            }
        }
        return $data;
    }

    public function jsTranslationAction()
    {
        $front = $this->getFrontController();
        $moduleDirectory = $front->getModuleDirectory($this->getRequest()->getParam('moduleName'));

        $transFile = $moduleDirectory . '/languages/js-translations.php';
        $jsTranslations = array();
        if (file_exists($transFile)) {
            $jsTranslations = include $transFile;
        }

        $headers = array();
        if ($this->_applyStrongCache) {
            $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', time()) . ' GMT';
        }
        $headers['Content-type'] = 'application/x-javascript';
        $this->_sendHeaders($headers);

        $aLines = array();

        foreach ($jsTranslations as $literal) {
            $key = str_replace(array('\'', '"'), '', (string) $literal);
            $value = $this->_helper->translate($literal);

            if (is_array($value)) {
                $value = $value[0];
            }

            $value = str_replace(
                array('\\\'', '"'),
                array('\'', '\"'),
                (string) $value
            );

            $aLines[] = '"'.$key.'" : "'.$value.'"';
        }

        echo "/*\n *\t[".$this->getRequest()->getParam('moduleName')."]\n *\tTranslation File\n */\n";
        echo "(function doLoad() { if (!window.jQuery || !window.jQuery.addTranslation)";
        echo "{ setTimeout(doLoad,100); return };";
        echo "$.addTranslation({\n\t";
        echo implode(",\n\t", $aLines);
        echo "\n});";
        echo "\n})();";
    }

    protected function _sendHeaders($headers = array())
    {
        $response = $this->getResponse();

        foreach ($this->_defaultHeaders as $key => $value) {
            if (!isset($headers[$key])) {
                $response->setHeader($key, $value, true);
            }
        }

        foreach ($headers as $key => $value) {
            $response->setHeader($key, $value, true);
        }

        $response->sendHeaders();
    }
}

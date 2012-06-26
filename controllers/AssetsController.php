<?php

class Klear_AssetsController extends Zend_Controller_Action
{
    protected $_defaultHeaders;
    protected $_applyStrongCache;

    public function init()
    {

        $this->_applyStrongCache = ("production" === APPLICATION_ENV);
        $this->_applyStrongCache = true;

        $this->_helper->layout->disableLayout();
        $this->_helper->getHelper('viewRenderer')->setNoRender();

        $this->_defaultHeaders = array(
            'Pragma' => 'public',
            'Cache-control' => 'maxage=' . 60*60*24*30, // ~1 Month
            'Expires' => gmdate('D, d M Y H:i:s', (time() + 60*60*24*30)) . ' GMT'
        );
    }

    protected function _buildPath($base)
    {
        $front = $this->getFrontController();
        $moduleDirectory = $front->getModuleDirectory($this->getRequest()->getParam('moduleName'));
        if (strpos($this->getRequest()->getParam("file"), 'translation/')!==false) {
            $this->_jsModuleTranslation($moduleDirectory);
            exit;
        }
        return $moduleDirectory . $base . $this->getRequest()->getParam("file");
    }

    protected function _getFileExtension($file)
    {
        $pathInfo = pathinfo($file);
        return $pathInfo['extension'];
    }

    protected function _returnFile($file)
    {
        /*
         * Dejamos pasar a las imágenes de las librerías externas.
        * Cabeceras de ficheros CSS JS según extensión
        */
        if (file_exists($file)) {

            if (strpos(mime_content_type($file), 'image') !== false) {

                return $this->_sendImage($file);

            } else {

                $this->_compress(
                    $file,
                    $this->_getFileExtension($file)
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
        $pluginClass = "Klear_Model_Css_";
        $pluginName = $this->getRequest()->getParam('plugin');
        $pluginParts = explode('-', $pluginName);

        foreach ($pluginParts as $part) {
            $pluginClass.= ucfirst($part);
        }

        if (!class_exists($pluginClass)) {
            exit;
        }

        $plg = new $pluginClass;

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
        $image = new Imagick($file);
        $hash = $image->getImageSignature();

        $response = $this->getResponse();

        if ($this->_hashMatches($hash)) {
            $response->setHttpResponseCode(304);
            return;
        }

        /* FIXME: Try not to instanciate Imagick on every request */
        $format = 'image/' . strtolower($image->getImageFormat());

        $headers = array();
        if ($this->_applyStrongCache) {
            $headers['ETag'] = $hash;
        }
        $headers['Content-type'] = $format;
        $headers['Content-length'] = filesize($file);
        $this->_setHeaders($headers);

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
        $this->_setHeaders($headers);

        readFile($file);
    }

    protected function _isUnmodifiedFile($lastModifiedTime)
    {
        $modifiedHeader = $this->getRequest()->getHeader('IF-MODIFIED-SINCE');
        if ($modifiedHeader) {
            if ($lastModifiedTime == strtotime($modifiedHeader)) {
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
            return;
        }

        $this->getFrontController()->setParam('disableOutputBuffering', true);

        $cache = $this->_getFileCache($file);

        $id = sha1($file);

        $raw = $cache->load($id);
        $headers = $cache->load("headers" . $id);

        if ((false === $raw) || (false === $headers)) {

            $raw = $this->_getContents($file, $type);

            switch(strtolower($type)) {
                case "js":
                    $fileContentType = 'application/x-javascript';
                    break;
                case "css":
                    $fileContentType = 'text/css';
                    break;
                case "html":
                case "htm":
                    $fileContentType = 'text/html';
                    break;
            }

            $headers = array();
            if ($this->_applyStrongCache) {
                $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', $lastModifiedTime) . ' GMT';
            }
            $headers['Content-type'] = $fileContentType;
            $headers['Content-length'] = mb_strlen($raw);

            $cache->save($raw, $id);
            $cache->save($headers, 'headers' . $id);
        }

        $this->_setHeaders($headers);
        echo $raw;
    }

    protected function _getFileCache($file)
    {
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
            'File',
            $frontendOptions,
            $backendOptions
        );

        return $cache;
    }

    protected function _getContents($file, $type)
    {
        $data = file_get_contents($file);

        if ($this->_applyStrongCache) {
            switch(strtolower($type)) {
                case "js":
                    $minifier = new Iron_Minify_JsMin($data);
                    break;
                case "css":
                    $minifier = new Iron_Minify_CssCompressor($data);
                    break;
                default:
                    throw new Zend_Exception("Minifier not properly called");
            }
            $data = $minifier->min();
        }
        return $data;
    }

    protected function _jsModuleTranslation($directory)
    {
        $transFile = $directory . '/languages/js-translations.php';
        $jsTranslations = array();
        if (file_exists($transFile)) {
            $jsTranslations = include $transFile;
        }

        $headers = array();
        if ($this->_applyStrongCache) {
            $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', time()) . ' GMT';
        }
        $headers['Content-type'] = 'application/x-javascript';
        $this->_setHeaders($headers);

        $aLines = array();
        foreach ($jsTranslations as $literal) {
            $key = str_replace(array('\'', '"'), '', $literal);
            $translateMethod = "translate";
            $value = $this->view->{$translateMethod}($literal);
            $value = str_replace(
                array('\\\'', '"'),
                array('\'', '\"'),
                $value
            );
            $aLines[] = '"'.$key.'" : "'.$value.'"';
        }
        echo "/*\n *\t[".$this->getRequest()->getParam('moduleName')."]\n *\tTranslation File\n */\n";
        echo "$.addTranslation({\n\t";
        echo implode(",\n\t", $aLines);
        echo "\n});";
    }

    protected function _setHeaders($headers)
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


<?php

class Klear_AssetsController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->getHelper('viewRenderer')->setNoRender();

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

    public function jsAction()
    {
        $jsFile = $this->_buildPath('/assets/js/');
        $this->_compress($jsFile, "js");
    }

    public function cssAction()
    {
        $cssFile = $this->_buildPath('/assets/css/');
        $this->_compress($cssFile, "css");
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

        // TODO: Fix
        /* FIXME: Fix what?? :S */
        /* TOBEFIXED: Instanciate  Imagick on every image request?? very bad!!! :'( */
        $format = 'image/' . strtolower($image->getImageFormat());

        $response = $this->getResponse();
        $request = $this->getRequest();

        if (($request->getHeader('If-None-Match'))
           && ($request->getHeader('If-None-Match') == $hash)) {
            $response->setHttpResponseCode(304);
                return;
        }

        if ("production" === APPLICATION_ENV) {
            $response->setHeader('ETag', $hash);
        }

        $response->setHeader('Cache-control', 'maxage=' . 60*60*24*30, true);
        $response->setHeader('Content-type', $format);
        $response->setHeader('Content-length', filesize($file));
        readFile($file);
    }


    protected function _sendRaw($file)
    {

        $lastModifiedTime = filemtime($file);

        $response = $this->getResponse();
        $request = $this->getRequest();

        if (($request->getHeader('IF-MODIFIED-SINCE'))
                && (strtotime($request->getHeader('IF-MODIFIED-SINCE')) == $lastModifiedTime)) {
            $response->setHttpResponseCode(304);
            return;
        }

        $response = $this->getResponse();
        $request = $this->getRequest();



        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-control', 'maxage=' . 60*60*24*30, true);

        if ("production" === APPLICATION_ENV) {
            $response->setHeader(
                'Last-Modified',
                gmdate('D, d M Y H:i:s', $lastModifiedTime) . ' GMT',
                true
            );
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $format = $finfo->file($file);


        $response->setHeader('Cache-control', 'maxage=' . 60*60*24*30, true);
        $response->setHeader('Content-type', $format);
        $response->setHeader('Content-length', filesize($file));
        readFile($file);
    }

    public function _compress($file, $type)
    {
        $lastModifiedTime = filemtime($file);

        // La "firma" del fichero contendrá la fecha de última modificación
        // $hashTag => Se aplica este tag a la cache para eliminar de la cache los ficheros viejos

        $hashTag = str_replace(array("/",".","-"), "_", basename($file));
        $hash = $hashTag . '__' . date('d_m_Y_H_i_s', $lastModifiedTime);

        $response = $this->getResponse();
        $request = $this->getRequest();

        if (($request->getHeader('IF-MODIFIED-SINCE'))
           && (strtotime($request->getHeader('IF-MODIFIED-SINCE')) == $lastModifiedTime)) {
            $response->setHttpResponseCode(304);
            return;
        }

        $this->getFrontController()->setParam('disableOutputBuffering', true);

        $frontendOptions = array(
            'lifetime' => null, // Forever! - mimetype is implicit in cache signature!
            'memorize_headers' => array(
                'content-type',
                'content-length',
                'pragma',
                'cache-control',
                'last-modified'
            ),
            'default_options' => array(
                'tags'=>array($hashTag)
            )
        );

        $backendOptions = array(
            'cache_dir' => APPLICATION_PATH . '/cache/'
        );

        $cache = Zend_Cache::factory(
            'Page',
            'File',
            $frontendOptions,
            $backendOptions
        );

        if (!$cache->start($hash)) {
            $data = $this->_getContents($file, $type);

            $response->setHeader('Pragma', 'public', true);
            $response->setHeader('Cache-control', 'maxage=' . 60*60*24*30, true);

            if ("production" === APPLICATION_ENV) {
                $response->setHeader(
                    'Last-Modified',
                    gmdate('D, d M Y H:i:s', $lastModifiedTime) . ' GMT',
                    true
                );
            }

            switch(strtolower($type)) {
                case "js":
                    $fileContentType = 'application/x-javascript';
                    break;
                case "css":
                    $fileContentType = 'text/css';
                    break;
            }

            $response->setHeader('Content-type', $fileContentType);
            $response->setHeader('Content-length', strlen($data));
            $response->sendHeaders();
            echo $data;

            // Antes de salvar, elimino todos los "tags" de este fichero
            $cache->clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                array($hashTag)
            );
            $cache->save($data);
        }
    }

    protected function _getContents($file, $type)
    {
        $data = file_get_contents($file);
        if ("production" === APPLICATION_ENV) {
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
    
    public function _jsModuleTranslation($directory)
    {
        $transFile = $directory . '/languages/js-translations.php';
        $jsTranslations = array();
        if (file_exists($transFile)) {
            $jsTranslations = include $transFile;
        }
        $response = $this->getResponse();
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-control', 'maxage=' . 60*60*24*30, true);
        if ("production" === APPLICATION_ENV) {
            $response->setHeader(
                'Last-Modified',
                gmdate('D, d M Y H:i:s', $lastModifiedTime) . ' GMT',
                true
            );
        }
        $fileContentType = 'application/x-javascript';
        $response->setHeader('Content-type', $fileContentType);
        $response->sendHeaders();
        $aLines = array();
        foreach ($jsTranslations as $literal) {
            $key = str_replace(array('\'', '"'), '', $literal);
            $translateMethod = "translate";
            $value = $this->view->{$translateMethod}($literal);
            $value = str_replace(
                    array('\\\'', '"'),
                    array('\'', '\"'),
                    $value);
            $aLines[] = '"'.$key.'" : "'.$value.'"';
        }
        echo "/*\n *\t[".$this->getRequest()->getParam('moduleName')."]\n *\tTranslation File\n */\n";
        echo "$.addTranslation({\n\t";
        echo implode(",\n\t", $aLines);
        echo "\n});";
    }
    
}


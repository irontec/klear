<?php

class Klear_AssetsController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->getHelper('viewRenderer')->setNoRender();
    }

    
    protected function _buildPath($base) {
        $front = $this->getFrontController();
        return $front->getModuleDirectory() . $base . $this->getRequest()->getParam("file");
    }
    
    public function jsAction() {
    	
    	$jsFile = $this->_buildPath('/assets/js/');
    	$this->_compress($jsFile,"js");
    }
    
    
    
    public function cssAction() {
    	
    	$cssFile = $this->_buildPath('/assets/css/');
    	$this->_compress($cssFile,"css");
    }
    
    
    public function imagecssAction() {
        $imgFile = $this->_buildPath('/assets/css/');
        return $this->_imgAction($imgFile);
                
    }
    
    protected function _imgAction($file) {
        
        // To-DO: Cachear propiedades de cada imagen?
        $image = new imagick($file);
        $hash = $image->getImageSignature();
        //TO-DO: Fix 
        $format = 'image/' . strtolower($image->getImageFormat()); 
        
        $response = $this->getResponse();
        $request = $this->getRequest();
        
    	if (($request->getHeader('If-None-Match')) &&
    			($request->getHeader('If-None-Match') == $hash)
    		)
    		{
    			$response->setHttpResponseCode(304);
    			return;
    	}
        
    	$response->setHeader('ETag',$hash);
		$response->setHeader('Cache-control', 'maxage=' . 60*60*24*30, true);
        $response->setHeader('Content-type',$format);	
	   	$response->setHeader('Content-length',filesize($file));
        readFile($file);	
        
    }
    
    public function _compress($file,$type)
    {
    	
    	switch(strtolower($type)) {
    		case "js":
    			$className = "Iron_Minify_JsMin";
    			$method = "min";
    			$file_content_type = 'application/x-javascript';
    			break;
    		case "css":
    			$className = "Iron_Minify_CssCompressor";
    			$method = "min";
    			$file_content_type = 'text/css';
    			break;
    		default:
    			Throw new Zend_Exception("Compresor not properly called");
    	}
    	
    	
    	$lastModifiedTime = filemtime($file);
    	
    	// La "firma" del fichero contendrá la fecha de última modificación
    	// $hashTag => Se aplica este tag a la cache para eliminar de la cache los ficheros viejos
    	
    	$hashTag = str_replace(array("/",".","-"),"_",basename($file));
    	$hash = $hashTag . '__' . date('d_m_Y_H_i_s',$lastModifiedTime);
    	
    	$response = $this->getResponse();
    	$request = $this->getRequest();
    	
    	if (($request->getHeader('IF-MODIFIED-SINCE')) &&
    			(strtotime($request->getHeader('IF-MODIFIED-SINCE')) == $lastModifiedTime)
    		)
    		{
    		
    			$response->setHttpResponseCode(304);
    			return;
    	}
    	
    	$this->getFrontController()->setParam('disableOutputBuffering', true);
    	
    	$frontendOptions = array(
    			'lifetime' => null, // Forever! - filemtype is implicit in cache signature!
    			'memorize_headers' => array('content-type', 'content-length','pragma','cache-control','last-modified'),
    	        'default_options' => array(
    	            'tags'=>array($hashTag)
    	        )
    	
    	
    	);
    	
    	$backendOptions = array(
    			'cache_dir' => APPLICATION_PATH . '/cache/'
    			
    	);
    	
    	$cache = Zend_Cache::factory('Page',
    			'File',
    			$frontendOptions,
    			$backendOptions);
    	
    	
		if (!$cache->start($hash)) {
		
    		$min = new $className(file_get_contents($file));
    		$data = $min->{$method}();
    	    
    		$response->setHeader('Pragma', 'public', true);
		    $response->setHeader('Cache-control', 'maxage=' . 60*60*24*30, true);
		    $response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $lastModifiedTime).' GMT', true);
		    $response->setHeader('Content-type',$file_content_type);	
	    	$response->setHeader('Content-length',strlen($data));
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
  


}


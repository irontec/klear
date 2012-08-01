<?php
class Klear_Model_Css_SilkExtended //TODO klear_Model_Css_Abstract
{
    /**
     *
     * @var Zend_Config
     */
    protected $_config;

    protected $_generate;
    protected $_iconsPath;
    protected $_iconsCache;

    protected $_outputName = 'silk-extended-sprite';
    protected $_size = 18;

    public function __construct(Zend_Config $config)
    {
        if (!$config) {
            throw new Klear_Exception_MissingConfiguration('No extended css config found');
        }

        $this->_config = $config;

        $front = Zend_Controller_Front::getInstance();
        $this->_generate = $front->getRequest()->getParam('generate', false);

        $this->_iconsPath = dirname(APPLICATION_PATH) . '/public' . $config->silkExtendedIconPath;
        $this->_iconsCache = dirname($this->_iconsPath) . '/cache';

        $this->_getIcons();
    }

    protected function _getFiles()
    {
        $classClean = array(
            '.png' => '',
            '_' => '-',
            ' ' => '-'
        );
        $dir = opendir($this->_iconsPath);
        while (($file = readdir($dir)) !== false) {
            $pathInfo = pathinfo($file);
            if ($pathInfo['extension'] == 'png') {
                $files[$file] = str_replace(
                    array_keys($classClean),
                    array_values($classClean),
                    $file
                );
            }
        }
        return $files;
    }

    protected function _generateCache()
    {
        $files = $this->_getFiles();

        $col = $row = $xPos = $yPos = 0;
        $x = $y = ceil(sqrt(sizeof($files)));
        $xWidth = $yHeight = (int)$this->_size * (int)$x;

        $base = new Imagick();
        $base->newImage($xWidth, $yHeight, new ImagickPixel('transparent'));
        $base->setImageFormat('png32');

        $cssBuffer = "";
        foreach ($files as $pic => $data) {
            $file = $this->_iconsPath . '/' . $pic;
            if (file_exists($file)) {
                if ($col == $x) {
                    $col = $xPos = 0;
                    $row++;
                    $yPos = $row * ($this->_size);
                }
                $im = new Imagick($file);

                $base->setImageColorspace($im->getImageColorspace());
                $base->compositeImage($im, $im->getImageCompose(), $xPos+1, $yPos+1);

                $im->clear();
                $im->destroy();

                $cssBuffer .= "\n.ui-silk-". $data . "\n{\n";
                $cssBuffer .= "\tbackground-image: url(./" . $this->_outputName . ".png);";
                $cssBuffer .= "background-position: -".$xPos."px -".$yPos."px;\n";
                $cssBuffer .= "}\n";

                $xPos+=$this->_size;
                $col++;
            }
        }

        $base->writeImage($this->_iconsCache . "/" . $this->_outputName . ".png");
        $base->clear();
        $base->destroy();

        $cssFile = fopen($this->_iconsCache . "/" . $this->_outputName . ".css", 'w');

        fwrite($cssFile, $cssBuffer);
        fclose($cssFile);
    }

    protected function _getIcons()
    {
        if ($this->_generate !== false) {
            if (is_dir($this->_iconsPath) && is_dir($this->_iconsCache) ) {
                $this->_generateCache();
            }
        }
    }

    public function getCssFile($fileName)
    {
        return $this->_iconsCache . "/" . $this->_outputName . ".css";
    }

    public function getPngFile($fileName)
    {
        return $this->_iconsCache . "/" . $this->_outputName . ".png";
    }
}
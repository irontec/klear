<?php

/**
 * Clase que instancia ficheros de "cabecera" de secciones, y se encarga de redireccionar al módulo correspondiente.
 * @author jabi
 *
 */
class Klear_Model_SectionConfig
{
    protected $_selectedModule;

    // Nombre de la clase de configuración del módulo
    protected $_moduleConfigClass;

    public function setFile($file)
    {

        $filePath = 'klear.yaml://' . $file;


        /*
         * Carga configuración de la sección cargada según la request.
        */
        $this->_config = new Zend_Config_Yaml(
                $filePath,
                APPLICATION_ENV,
                array(
                        "yamldecoder"=>"yaml_parse"
                )
        );

        $this->setConfig($this->_config);
    }

    public function setConfig(Zend_Config $config)
    {
        // TODO: Control de errores, configuración mal seteada
        $this->_selectedModule = $config->main->module;
        $this->_moduleConfigClass = ucfirst(($this->_selectedModule)) . '_Model_MainConfig';
    }

    public function isValid()
    {
        return
            method_exists($this->_moduleConfigClass, 'setConfig');
    }

    public function factoryModuleConfig()
    {

        $moduleConfig = new $this->_moduleConfigClass;
        $moduleConfig->setConfig($this->_config);
        return $moduleConfig;
    }
}

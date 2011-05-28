<?php

/**
 * Easily adds activable functionality to a record.
 *
 * @package     sfDoctrineActivablePlugin
 * @subpackage  template
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 * @author      Ali hichem <ali.hichem@mail.com>
 */
class Doctrine_Template_Activable extends Doctrine_Template
{

    /**
     * Array of Activable options
     *
     * @var string
     */
    protected $_options = array('name','alias','type','length','options','indexName','cascade');

    /**
     * __construct
     *
     * @param string $array
     * @return void
     */
    public function __construct(array $options = array())
    {
        $_options = array();
        foreach ($this->_options as $key)
        {
            $_options[$key] = sfConfig::get("app_activable_{$key}") ;
        }
        $this->_options = Doctrine_Lib::arrayDeepMerge($_options, $options);
    }

    /**
     * Set table definition for activable behavior
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $name = $this->_options['name'];

        if ($this->_options['alias'])
        {
            $name .= ' as ' . $this->_options['alias'];
        }

        $this->hasColumn($name, $this->_options['type'], $this->_options['length'], $this->_options['options']);

        $this->addListener(new Doctrine_Template_Listener_Activable($this->_options));
    }

}

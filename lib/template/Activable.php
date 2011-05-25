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
    protected $_options = array('name' => 'is_active',
        'alias' => null,
        'type' => 'boolean',
        'length' => 1,
        'options' => array(
            "default" => true
        ),
        'indexName' => 'activable',
        'cascade' => array(
             'down' => true,
             'up' => false,
        )
    );

    /**
     * __construct
     *
     * @param string $array
     * @return void
     */
    public function __construct(array $options = array())
    {
        $this->_options = Doctrine_Lib::arrayDeepMerge($this->_options, $options);
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

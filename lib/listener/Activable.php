<?php

/**
 * Easily add active state to records
 *
 * @package     sfDoctrineActivablePlugin
 * @subpackage  listener
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 * @author      Ali hichem <ali.hichem@mail.com>
 */
class Doctrine_Template_Listener_Activable extends Doctrine_Record_Listener
{

    /**
     * Array of activable options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * __construct
     *
     * @param array $options
     * @return void
     */
    public function __construct(array $options)
    {
        $this->_options = $options;
    }

    /**
     * Pre insert method
     *
     * @param Doctrine_Event $event
     * @return void
     */
    public function preInsert(Doctrine_Event $event)
    {

    }

    /**
     * When a activable object is desactivated, desactivate all child objects
     *
     * @param string $Doctrine_Event 
     * @return void
     */
    public function postDelete(Doctrine_Event $event)
    {
        $fieldName = $this->_options['name'];
        $object = $event->getInvoker();
    }

    /**
     * Implement preDqlSelect() hook and add the activable flag to all queries for which this model
     * is being used in.
     *
     * @param Doctrine_Event $event
     * @return void
     */
    public function preDqlSelect(Doctrine_Event $event)
    {
        $params = $event->getParams();
        $field = $params['alias'] . '.' . $this->_options['name'];
        $query = $event->getQuery();

        // We only need to add the restriction if:
        // 1 - We are in the root query
        // 2 - We are in the subquery and it defines the component with that alias
        if ((!$query->isSubquery() || ($query->isSubquery() && $query->contains(' ' . $params['alias'] . ' '))) && !$query->contains($field))
        {
            if ($this->_options['type'] == 'boolean')
            {
                $query->addPendingJoinCondition(
                        $params['alias'], $field . ' = ' . $query->getConnection()->convertBooleans(true)
                );
            }
        }
    }

    public function preSave(Doctrine_Event $event)
    {
        parent::preSave($event);
    }

    public function postSave(Doctrine_Event $event)
    {
        if (array_key_exists($this->_options['name'], $event->getInvoker()->_modified))
        {
            $cascade = FALSE ;
            $value = $event->getInvoker()->get($this->_options['name']);
            if ($value == TRUE && $this->_options['cascade']['up'] == true)
            {
                $cascade = TRUE ;
            }
            elseif ($value == FALSE && $this->_options['cascade']['down'] == true)
            {
                $cascade = TRUE ;
            }
            if($cascade)
            {
                $class = get_class($event->getInvoker()) ;
                $conn = Doctrine::getConnectionByTableName($class);
                $relations = Doctrine_core::getTable($class)->getRelations();
                foreach ($relations as $relation)
                {
                    $_class = $relation->getClass();
                    if( $relation->getType() == Doctrine_Relation::MANY &&
                        array_key_exists('Doctrine_Template_Activable', $relation->getTable()->getTemplates()) &&
                        Doctrine_core::getTable($_class)->hasColumn($relation->getForeign()))
                    {
                        $_options = $relation->getTable()->getTemplate('Doctrine_Template_Activable')->getOptions() ;
                        $items = Doctrine_core::getTable($_class)
                                            ->findBy($relation->getForeignColumnName(),$event->getInvoker()->getId());
                        foreach ($items as $item)
                        {
                            $item->set($_options['name'],$value);
                            $item->save();
                        }
                    }
                }
            }
        }
    }

}

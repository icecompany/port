<?php
use Joomla\CMS\Table\Table;
defined('_JEXEC') or die;

/**
 * Таблица с названиями каталогов стендов
 * @since   1.2.3
 * @version 1.2.3
 */

class TableProjectsCatalogtitles extends Table
{
    var $id = null;
    var $title = null;

    public function __construct(JDatabaseDriver $db)
    {
        parent::__construct('#__prj_catalog_titles', 'id', $db);
    }

    public function load($keys = null, $reset = true)
    {
        return parent::load($keys, $reset);
    }

    public function bind($src, $ignore = array())
    {
        foreach ($src as $field => $value)
        {
            if (isset($this->$field)) $this->$field = $value;
        }
        return parent::bind($src, $ignore);
    }
    public function save($src, $orderingFilter = '', $ignore = '')
    {
        return parent::save($src, $orderingFilter, $ignore);
    }

    public function store($updateNulls = true)
    {
        return parent::store(true);
    }
}
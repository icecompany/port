<?php
use Joomla\CMS\Table\Table;
defined('_JEXEC') or die;

/*
 * Таблица контактных данных об экспоненте
 */

class TableProjectsAddresses extends Table
{
    var $id = null;
    var $exbID = null;
    var $indexcode = null;
    var $indexcode_fact = null;
    var $addr_legal_street = null;
    var $addr_legal_home = null;
    var $addr_fact_street = null;
    var $addr_fact_home = null;
    var $phone_1 = null;
    var $phone_1_additional = null;
    var $phone_2 = null;
    var $phone_2_additional = null;
    var $phone_1_comment = null;
    var $phone_2_comment = null;
    var $fax = null;
    var $fax_additional = null;
    var $email = null;
    var $site = null;
    var $director_name = null;
    var $director_post = null;
    var $contact_person = null;
    var $contact_data = null;

    public function __construct(JDatabaseDriver $db)
    {
        parent::__construct('#__prj_exp_contacts', 'id', $db);
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
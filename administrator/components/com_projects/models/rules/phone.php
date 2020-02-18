<?php
use Joomla\CMS\Form\FormRule;
defined('_JEXEC') or die;

class JFormRulePhone extends FormRule
{
    protected $regex = '^\+\d{1,3}\s\(\d{2,5}\)\s\d{1,3}-\d{2}-\d{2}$';

    public function test(\SimpleXMLElement $element, $value, $group = null, \Joomla\Registry\Registry $input = null, \Joomla\CMS\Form\Form $form = null)
    {
        return (!empty($value)) ? parent::test($element, $value, $group, $input, $form) : true;
    }
}
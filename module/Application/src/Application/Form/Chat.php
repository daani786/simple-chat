<?php
namespace Application\Form;

class Chat extends Form
{
    private $name = 'chat';

    protected $attributes = array (
        'autocomplete' => 'off',
        'method' => 'post',
        'class' => 'form parsley-form',
        'id' => "chat",
    );

    public $elements_data = array (
        array (
            'name' => 'id',
            'type' => 'Hidden',
        ),
        array(
            'name' => 'message',
            'attributes' => array(
                'id' => 'message',
                'type' => 'text',
                'error_msg' => 'Enter Valid Message',
                'class' => 'form-control',
                'data-parsley-required' => "true",
                'placeholder' => "Let's type something here!"
            ),
            'options' => array(
                'label' => 'Message',
            ),
            'validation' => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim')
                )
            )
        ),
        array (
            'name' => 'submit',
            'attributes' => array (
                'type'  => 'submit',
                'value' => 'Send',
                'id' => 'submitbutton',
            )
        )
    );
}
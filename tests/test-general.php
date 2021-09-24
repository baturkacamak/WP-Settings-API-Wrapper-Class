<?php


use PHPUnit\Framework\TestCase;

class TestGeneral extends TestCase
{
    /**
     * @var string[][]
     */
    private $array;

    public function __construct()
    {
        parent::__construct();
        $this->array = [
            'hd_text_setting' => [
                'title'    => 'Text Input',
                'type'     => 'text',
                'default'  => 'Hello World121!',
                'desc'     => 'Example Text Input',
                'sanit'    => 'nohtml',
                'no_value' => '',
            ],
        ];
    }


    public function testHasValue()
    {
        $output   = ml_atv('hd_text_setting/type', $this->array);
        $expected = 'text';

        $this->assertEquals($expected, $output);
    }

    public function testGetDefaultValue()
    {
        $output   = ml_atv('hd_text_setting/type2', $this->array, 'default');
        $expected = 'default';

        $this->assertEquals($expected, $output);
    }

    public function testGetEmptyValue()
    {
        $output   = ml_atv('hd_text_setting/no_value', $this->array, 'default', true);
        $expected = '';

        $this->assertEquals($expected, $output);
    }

    public function testGetDefaultIfEmptyValue()
    {
        $output   = ml_atv('hd_text_setting/no_value', $this->array, 'default');
        $expected = 'default';

        $this->assertEquals($expected, $output);
    }
}

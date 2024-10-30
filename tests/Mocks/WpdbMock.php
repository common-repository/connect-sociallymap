<?php

namespace Mocks;

class WpdbMock
{
    public $prefix;

    public $data;

    public function insert(... $data)
    {
        $this->data = $data;

        return true;
    }
    public function query()
    {

    }
    public function get_results()
    {

    }
    public function get_var()
    {

    }
    public function get_charset_collate()
    {

        return 'nonVide';
    }
}
<?php

namespace App\FormFields;

use TCG\Voyager\FormFields\AbstractHandler;

class EmailFromField extends AbstractHandler
{
    protected $codename = 'email';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('vendor.voyager.partials.email-form-field', [
            'row' => $row,
            'options' => $options,
            'dataType' => $dataType,
            'dataTypeContent' => $dataTypeContent
        ]);
    }
}

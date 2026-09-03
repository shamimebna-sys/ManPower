<?php

namespace App\FormFields;

use TCG\Voyager\FormFields\AbstractHandler;

class MobileFromField extends AbstractHandler
{
    protected $codename = 'mobile';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('vendor.voyager.partials.mobile-form-field', [
            'row' => $row,
            'options' => $options,
            'dataType' => $dataType,
            'dataTypeContent' => $dataTypeContent
        ]);
    }
}

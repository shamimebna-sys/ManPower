<?php

namespace App\FormFields;

use TCG\Voyager\FormFields\AbstractHandler;

class LinkFromField extends AbstractHandler
{
    protected $codename = 'link';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('vendor.voyager.partials.link-form-field', [
            'row' => $row,
            'options' => $options,
            'dataType' => $dataType,
            'dataTypeContent' => $dataTypeContent
        ]);
    }
}

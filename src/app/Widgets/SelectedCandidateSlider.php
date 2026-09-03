<?php

namespace App\Widgets;

use Illuminate\Support\Facades\Auth;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Widgets\BaseDimmer;

class SelectedCandidateSlider extends BaseDimmer
{
    protected $config = [];
    public $candidate = false;

    public function run()
    {
        $this->candidate = \DB::select(" SELECT sc.*, c.name, c.passport_no, c.half_photo_file_path
                         FROM selected_candidates AS sc
                         INNER JOIN candidates AS c  ON c.id = sc.candidate_id
                        ORDER BY sc.id DESC");


        $sponsors = [];
        if($this->candidate){
            foreach ($this->candidate as $s){
                $sponsors[] = [
                    'name' => $s->name,
                    'position' => strtoupper($s->position),
                    'passport_no' =>$s->passport_no,
                    'image' =>!empty($s->half_photo_file_path)?Voyager::image($s->half_photo_file_path):'https://placehold.co/150?text='.$s->name,
                    'website' => '#'
                ];
            }
        }else{
            $sponsors = [];
        }
        return view('voyager::widgets.selected-candidate-slider', [
            'sponsors' => $sponsors,
            'config' => $this->config,
        ]);
    }

    public function shouldBeDisplayed()
    {
        return true;
        return Auth::user()->can('browse', Voyager::model('User'));
    }
}

@php
    $candidate_id = request()->get('candidate_id');
@endphp
@if($candidate_id)
    <style>
        .candidate-nav-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 24px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
            padding: 18px !important;
            margin-bottom: 24px;
        }

        .candidate-nav-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-bottom: 14px;
            padding-left: 6px;
        }

        .candidate-nav-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .candidate-nav-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #475569 !important;
            text-decoration: none !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            background: transparent;
            position: relative;
        }

        .candidate-nav-item-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .candidate-nav-item .nav-icon {
            width: 18px;
            height: 18px;
            color: #64748b;
            transition: all 0.25s ease;
        }

        .candidate-nav-item:hover {
            background: rgba(79, 70, 229, 0.04) !important;
            color: {{ config('voyager.primary_color', '#22A7F0') }} !important;
            border-color: rgba(79, 70, 229, 0.08);
            transform: translateX(4px);
        }

        .candidate-nav-item:hover .nav-icon {
            color: {{ config('voyager.primary_color', '#22A7F0') }};
        }

        .candidate-nav-item.active {
            background: linear-gradient(135deg, {{ config('voyager.primary_color', '#22A7F0') }} 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
            border-color: transparent;
        }

        .candidate-nav-item.active .nav-icon {
            color: #ffffff !important;
        }

        .candidate-nav-item .step-badge {
            font-size: 10px;
            font-weight: 700;
            background: #f1f5f9;
            color: #64748b;
            padding: 2px 8px;
            border-radius: 20px;
            transition: all 0.25s ease;
        }

        .candidate-nav-item.active .step-badge {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }
    </style>

    <div class="col-md-3">
        <div class="candidate-nav-card">
            <div class="candidate-nav-title">Navigation Steps</div>
            @php
                $tabs = [
                  [
                      'name' => 'Basic Info',
                      'active' => (Route::currentRouteName()=='voyager.candidates.edit' && !request()->has('stamp'))?'Y':'N',
                      'url' => route('voyager.candidates.edit', request()->get('candidate_id')).'?candidate_id='.request()->get('candidate_id'),
                      'icon' => '<svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>'
                  ],
                  [
                      'name' => 'Passport With Stamp',
                      'active' => (Route::currentRouteName()=='voyager.candidates.edit' && request()->has('stamp'))?'Y':'N',
                      'url' => route('voyager.candidates.edit', request()->get('candidate_id')).'?candidate_id='.request()->get('candidate_id').'&stamp=Y',
                      'icon' => '<svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>'
                  ],
                  [
                      'name' => 'Police Clearance',
                      'active' => (Route::currentRouteName()=='voyager.police-clearances.index')?'Y':'N',
                      'url' => route('voyager.police-clearances.index').'?candidate_id='.request()->get('candidate_id'),
                      'icon' => '<svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>'
                  ],
                  [
                      'name' => 'Medical & Ministry',
                      'active' => (Route::currentRouteName()=='voyager.medicals.index')?'Y':'N',
                      'url' => route('voyager.medicals.index').'?candidate_id='.request()->get('candidate_id'),
                      'icon' => '<svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>'
                  ],
                  [
                      'name' => 'Visa Immigration',
                      'active' => (Route::currentRouteName()=='voyager.visa-immigrations.index')?'Y':'N',
                      'url' => route('voyager.visa-immigrations.index').'?candidate_id='.request()->get('candidate_id'),
                      'icon' => '<svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h2a2.5 2.5 0 002.5-2.5V14a2 2 0 00-2-2h-.5a2 2 0 01-2-2V5a2 2 0 00-2-2H9.828a2 2 0 00-1.414.586l-.828.828A2 2 0 008 3.935z" /></svg>'
                  ],
                  [
                      'name' => 'Manpower',
                      'active' => (Route::currentRouteName()=='voyager.manpower-trainings.index')?'Y':'N',
                      'url' => route('voyager.manpower-trainings.index').'?candidate_id='.request()->get('candidate_id'),
                      'icon' => '<svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" /></svg>'
                  ],
                  [
                      'name' => 'Flight Schedule',
                      'active' => (Route::currentRouteName()=='voyager.flight-schedules.index')?'Y':'N',
                      'url' => route('voyager.flight-schedules.index').'?candidate_id='.request()->get('candidate_id'),
                      'icon' => '<svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>'
                  ],
                  [
                      'name' => 'ARC',
                      'active' => (Route::currentRouteName()=='voyager.arcs.index')?'Y':'N',
                      'url' => route('voyager.arcs.index').'?candidate_id='.request()->get('candidate_id'),
                      'icon' => '<svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>'
                  ],
                ];

                if(!in_array(\App\Helpers\CommonClass::user()->role_id,[101, 107, 109])){
                         $tabs[] = [
                             'name' => 'Labour Contract',
                             'active' => (Route::currentRouteName()=='voyager.labour-contracts.index')?'Y':'N',
                             'url' => route('voyager.labour-contracts.index').'?candidate_id='.request()->get('candidate_id'),
                             'icon' => '<svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>'
                         ];
                         $tabs[] = [
                             'name' => 'Document',
                             'active' => (Route::currentRouteName()=='voyager.candidate-documents.create')?'Y':'N',
                             'url' => route('voyager.candidate-documents.create').'?candidate_id='.request()->get('candidate_id'),
                             'icon' => '<svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>'
                         ];
                }
                if(!in_array(\App\Helpers\CommonClass::user()->role_id,[101, 107, 108, 109, 110])){
                        $tabs[] = [
                            'name' => 'Payment',
                            'active' => (Route::currentRouteName()=='voyager.payment-requests.create')?'Y':'N',
                            'url' => route('voyager.payment-requests.create').'?candidate_id='.request()->get('candidate_id'),
                            'icon' => '<svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>'
                        ];
                }
            @endphp

            <div class="candidate-nav-list">
                @foreach($tabs as $i=>$tab)
                    <a href="{{$tab['url']}}" class="candidate-nav-item {{($tab['active']=='Y')?'active':''}}">
                        <div class="candidate-nav-item-content">
                            {!! $tab['icon'] !!}
                            <span>{{$tab['name']}}</span>
                        </div>
                        <span class="step-badge">{{++$i}}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif

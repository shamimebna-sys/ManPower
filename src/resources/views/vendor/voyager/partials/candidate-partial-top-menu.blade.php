@php
    if(!isset($candidate_id) && empty($candidate_id)){
         $candidate_id = request()->get('candidate_id');
    }

    if(!$candidate_id && \App\Helpers\CommonClass::user()->role_id == 102){
        $candidate_id_1 = \App\Helpers\CommonClass::user()->profile_id;
    }
    $candidate = \App\Models\Candidate::find($candidate_id);
    if(!$candidate && isset($candidate_id)){
        $candidate_id = $candidate_id_1;
        $candidate = \App\Models\Candidate::find($candidate_id);
    }
@endphp

@if($candidate_id && $candidate)
    <style>
        .candidate-header-wrapper {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 24px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
            padding: 16px 24px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .candidate-profile-block {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
        }

        .candidate-avatar-frame {
            position: relative;
            width: 62px;
            height: 62px;
            border-radius: 50%;
            padding: 3px;
            background: linear-gradient(135deg, {{ config('voyager.primary_color', '#22A7F0') }} 0%, #4f46e5 100%);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .candidate-avatar-frame img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid #ffffff;
            object-fit: cover;
        }

        .candidate-info-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .candidate-name-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }

        .candidate-meta-badges {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .candidate-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-code {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .badge-status {
            background: rgba(34, 197, 94, 0.08);
            color: #166534;
            border: 1px solid rgba(34, 197, 94, 0.2);
            box-shadow: 0 1px 2px rgba(34, 197, 94, 0.05);
        }

        .progress-pipeline {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            flex-grow: 1;
            padding: 4px 0 10px 0;
            align-items: center;
        }

        /* Custom elegant scrollbar for progress bar */
        .progress-pipeline::-webkit-scrollbar {
            height: 5px;
        }
        .progress-pipeline::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 10px;
        }
        .progress-pipeline::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .progress-pipeline::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .pipeline-step-link {
            text-decoration: none !important;
            color: inherit !important;
            display: block;
            flex: 0 0 auto;
        }

        .pipeline-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 14px;
            min-width: 115px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            position: relative;
        }

        .pipeline-step-link:not(.disabled):hover .pipeline-step {
            border-color: {{ config('voyager.primary_color', '#22A7F0') }};
            background: rgba(34, 167, 240, 0.03);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }

        .pipeline-step-link.disabled {
            cursor: not-allowed;
            opacity: 0.65;
        }

        .pipeline-step.active {
            background: linear-gradient(135deg, {{ config('voyager.primary_color', '#22A7F0') }} 0%, #1d4ed8 100%) !important;
            border-color: transparent !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .pipeline-step.active .step-label {
            color: #ffffff !important;
        }

        .pipeline-step.active .step-status-icon {
            background: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
        }

        .step-label {
            font-size: 10px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            margin-bottom: 6px;
            transition: color 0.25s ease;
        }

        .step-status-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            transition: all 0.25s ease;
        }

        .step-status-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .step-status-icon.danger {
            background: rgba(239, 68, 68, 0.08);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.15);
        }

        .step-status-icon.neutral {
            background: #e2e8f0;
            color: #64748b;
            font-size: 9px;
        }

        /* Connecting arrow style indicator if needed */
        @media(max-width: 768px) {
            .candidate-header-wrapper {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>

    <div class="candidate-header-wrapper">
        <!-- Candidate Profile Block -->
        <div class="candidate-profile-block">
            <div class="candidate-avatar-frame">
                <img src="{{ !empty($candidate->half_photo_file_path) ? Voyager::image($candidate->half_photo_file_path) : '' }}" alt="{{ $candidate->name }}">
            </div>
            <div class="candidate-info-details">
                <h3 class="candidate-name-title">{{ $candidate->name }}</h3>
                <div class="candidate-meta-badges">
                    <span class="candidate-badge badge-code">{{ $candidate->code }}</span>
                    @php
                        $liveStatus = \App\Helpers\CommonClass::liveStatus($candidate->id);
                    @endphp
                    @if(!empty($liveStatus))
                        <span class="candidate-badge badge-status">{!! $liveStatus !!}</span>
                    @endif
                </div>
                @if(!empty($candidate->replacement_remarks))
                    <div class="candidate-replacement-logs" style="margin-top: 8px; font-size: 11px; line-height: 1.4;">
                        @foreach(explode(' | ', $candidate->replacement_remarks) as $log)
                            <div style="margin-bottom: 3px;">
                                <span class="label label-danger" style="display: inline-block; padding: 1px 4px; font-size: 8px; font-weight: bold; border-radius: 3px; background-color: #ef4444; color: #ffffff; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Replacement Log
                                </span>
                                <span style="font-weight: 600; color: #475569; margin-left: 4px;">{{ $log }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @php
            $role_id = \App\Helpers\CommonClass::user()->role_id;
            $is_labour_document_allowed = !in_array($role_id, [101, 107]);
            $is_payment_allowed = !in_array($role_id, [101, 107, 108]);

            $steps = [
                [
                    'label' => 'Registration',
                    'completed' => true,
                    'active' => (Route::currentRouteName() == 'voyager.candidates.edit' && !request()->has('stamp')),
                    'url' => route('voyager.candidates.edit', $candidate->id) . '?candidate_id=' . $candidate->id,
                    'allowed' => true
                ],
                [
                    'label' => 'CV Upload',
                    'completed' => ($candidate->cv_file_path && count(json_decode($candidate->cv_file_path)) > 0),
                    'active' => (Route::currentRouteName() == 'voyager.candidate-documents.create'),
                    'url' => $is_labour_document_allowed ? route('voyager.candidate-documents.create') . '?candidate_id=' . $candidate->id : '#',
                    'allowed' => $is_labour_document_allowed
                ],
                [
                    'label' => 'Class Group',
                    'completed' => (bool)$candidate->classGroup,
                    'active' => (Route::currentRouteName() == 'voyager.candidates.edit' && !request()->has('stamp') && $candidate->classGroup),
                    'url' => route('voyager.candidates.edit', $candidate->id) . '?candidate_id=' . $candidate->id,
                    'allowed' => true,
                    'custom_val' => $candidate->classGroup ? $candidate->classGroup->name : null
                ],
                [
                    'label' => 'Labour',
                    'completed' => (bool)\App\Helpers\CommonClass::labourStatus($candidate->id),
                    'active' => (Route::currentRouteName() == 'voyager.labour-contracts.index'),
                    'url' => $is_labour_document_allowed ? route('voyager.labour-contracts.index') . '?candidate_id=' . $candidate->id : '#',
                    'allowed' => $is_labour_document_allowed
                ],
                [
                    'label' => 'Police Clear.',
                    'completed' => (bool)\App\Helpers\CommonClass::policeClearanceStatus($candidate->id),
                    'active' => (Route::currentRouteName() == 'voyager.police-clearances.index'),
                    'url' => route('voyager.police-clearances.index') . '?candidate_id=' . $candidate->id,
                    'allowed' => true
                ],
                [
                    'label' => 'Visa',
                    'completed' => (bool)\App\Helpers\CommonClass::visaStatus($candidate->id),
                    'active' => (Route::currentRouteName() == 'voyager.visa-immigrations.index'),
                    'url' => route('voyager.visa-immigrations.index') . '?candidate_id=' . $candidate->id,
                    'allowed' => true
                ],
                [
                    'label' => 'BMET Train.',
                    'completed' => (bool)\App\Helpers\CommonClass::bmetStatus($candidate->id),
                    'active' => (Route::currentRouteName() == 'voyager.manpower-trainings.index'),
                    'url' => route('voyager.manpower-trainings.index') . '?candidate_id=' . $candidate->id,
                    'allowed' => true
                ],
                [
                    'label' => 'ARC',
                    'completed' => (bool)\App\Helpers\CommonClass::arcStatus($candidate->id),
                    'active' => (Route::currentRouteName() == 'voyager.arcs.index'),
                    'url' => route('voyager.arcs.index') . '?candidate_id=' . $candidate->id,
                    'allowed' => true
                ],
                [
                    'label' => 'Manpower',
                    'completed' => (bool)\App\Helpers\CommonClass::manpowerStatus($candidate->id),
                    'active' => (Route::currentRouteName() == 'voyager.payment-requests.create' && request()->get('bill_type') == 'manpower'),
                    'url' => $is_payment_allowed ? route('voyager.payment-requests.create') . '?candidate_id=' . $candidate->id . '&bill_type=manpower' : '#',
                    'allowed' => $is_payment_allowed
                ],
                [
                    'label' => 'Flight',
                    'completed' => (bool)\App\Helpers\CommonClass::flightStatus($candidate->id),
                    'active' => (Route::currentRouteName() == 'voyager.flight-schedules.index'),
                    'url' => route('voyager.flight-schedules.index') . '?candidate_id=' . $candidate->id,
                    'allowed' => true
                ]
            ];
        @endphp

        <!-- Progress Steps Pipeline -->
        <div class="progress-pipeline">
            @foreach($steps as $step)
                <a href="{{ $step['allowed'] ? $step['url'] : 'javascript:void(0);' }}" 
                   class="pipeline-step-link {{ !$step['allowed'] ? 'disabled' : '' }}"
                   @if(!$step['allowed']) title="Access Restricted" @endif>
                    <div class="pipeline-step {{ $step['active'] ? 'active' : '' }}">
                        <div class="step-label">{{ $step['label'] }}</div>
                        @if(isset($step['custom_val']) && $step['custom_val'])
                            <div class="step-status-icon success" style="width: auto; border-radius: 6px; padding: 2px 6px; font-size: 9px;" title="{{ $step['custom_val'] }}">
                                {{ strlen($step['custom_val']) > 8 ? substr($step['custom_val'], 0, 7) . '..' : $step['custom_val'] }}
                            </div>
                        @else
                            @if($step['completed'])
                                <div class="step-status-icon success" title="Completed">✔</div>
                            @else
                                <div class="step-status-icon danger" title="Pending">✖</div>
                            @endif
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif

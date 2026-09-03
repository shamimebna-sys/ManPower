<div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
    <table class="table table-striped table-hover table-bordered" style="margin-bottom: 0;">
        <thead>
            <tr style="background-color: #f9f9f9;">
                <th>Candidate</th>
                <th>Passport No</th>
                <th>Status</th>
                <th>Agent</th>
                <th>Agency</th>
                <th>Company</th>
                <th>License Position</th>
                <th>Company Status</th>
                <th>ARC NO.</th>
                <th>MP/Visa</th>
                <th>Labour Expire</th>
                <th>Live Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($candidates as $candidate)
                <tr style="background: {{ \App\Helpers\CommonClass::labourStatus($candidate->id, false, true) }}">
                    <td>
                        <a href="{{ route('voyager.candidates.edit', $candidate->id) }}?candidate_id={{ $candidate->id }}" target="_blank" style="font-weight: bold; color: #337ab7;">
                            ({{ $candidate->code }}) {{ $candidate->name }}
                        </a>
                        <div style="font-size: 11px; color: #666; margin-top: 2px;">
                            <strong>Email:</strong> {{ $candidate->email ?? 'N/A' }}<br>
                            <strong>Mobile:</strong> {{ $candidate->mobile ?? 'N/A' }}
                        </div>
                    </td>
                    <td>{{ $candidate->passport_no ?? 'N/A' }}</td>
                    <td>
                        @if($candidate->status == 'A')
                            <span class="label label-success">Active</span>
                        @elseif($candidate->status == 'P')
                            <span class="label label-warning">Pending</span>
                        @elseif($candidate->status == 'I')
                            <span class="label label-danger">Inactive</span>
                        @else
                            <span class="label label-default">{{ $candidate->status ?? 'N/A' }}</span>
                        @endif
                    </td>
                    <td>
                        @if($candidate->agent)
                            ({{ $candidate->agent->code }}) {{ $candidate->agent->name }}
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>{{ optional($candidate->agencier)->name ?? 'N/A' }}</td>
                    <td>{{ optional($candidate->companier)->name ?? 'N/A' }}</td>
                    <td>{{ optional($candidate->positionRelation)->title ?? 'N/A' }}</td>
                    <td>{{ $candidate->company_status ?? 'N/A' }}</td>
                    <td>{{ \App\Helpers\CommonClass::arcNo($candidate->id) ?: 'N/A' }}</td>
                    <td>
                        @if($candidate->VisaImmigration && count($candidate->VisaImmigration) > 0)
                            {{ $candidate->VisaImmigration[0]->visa_mp_no ?? 'N/A' }}
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>{{ \App\Helpers\CommonClass::labourStatus($candidate->id, true) ?: 'N/A' }}</td>
                    <td>{!! \App\Helpers\CommonClass::liveStatus($candidate->id) ?: 'N/A' !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 20px; color: #999;">
                        No candidates found in this class group.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

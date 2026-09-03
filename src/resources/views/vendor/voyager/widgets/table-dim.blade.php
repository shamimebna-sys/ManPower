@php 
    $widget_id = md5($table_title); 
@endphp

<style>
    .table-container {
        max-width: 100%;
        margin: 0 auto 24px auto;
        background: #ffffff;
        padding: 0;
        border-radius: 12px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 4px -1px rgba(15, 23, 42, 0.02);
        border: 1px solid rgba(226, 232, 240, 0.8);
        overflow: hidden;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s ease;
    }
    
    .table-container:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -3px rgba(15, 23, 42, 0.1), 0 4px 6px -2px rgba(15, 23, 42, 0.02);
    }

    .table-title {
        background: {{ config('voyager.primary_color', '#22A7F0') }};
        background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(0, 0, 0, 0.12) 100%);
        color: #ffffff;
        padding: 16px 20px;
        text-align: center;
        font-weight: 700;
        font-size: 14px;
        margin: 0;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-bottom: none;
    }

    .widget-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 10px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .btn-toggle-details {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
        outline: none;
    }

    .btn-toggle-details:hover {
        border-color: #6366f1;
        color: #6366f1;
        background: rgba(99, 102, 241, 0.05);
    }

    .btn-toggle-details i {
        font-size: 14px;
    }

    .custom-table {
        margin: 0;
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table thead {
        background: #f8fafc;
        color: #475569;
    }

    .custom-table thead th {
        padding: 12px 18px;
        font-weight: 600;
        text-align: left;
        border: none;
        border-bottom: 2px solid #e2e8f0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .custom-table tbody td {
        padding: 12px 18px;
        border: none;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        color: #334155;
        background: #ffffff;
        transition: background-color 0.15s ease;
    }

    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }

    .custom-table tbody tr {
        transition: background-color 0.15s ease;
    }

    .custom-table tbody tr:hover td {
        background: #f8fafc;
        color: #4f46e5;
    }

    .custom-table tbody tr td:empty::after {
        content: "-";
        color: #94a3b8;
    }

    .table-view-wrapper {
        height: 308px;
        overflow-y: auto;
    }

    /* Scrollbar styling for premium feel */
    .table-view-wrapper::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .table-view-wrapper::-webkit-scrollbar-track {
        background: #f8fafc;
    }
    .table-view-wrapper::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .table-view-wrapper::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-container {
            margin: 0 10px 15px 10px;
        }

        .table-title {
            font-size: 13px;
            padding: 12px;
        }

        .custom-table thead th,
        .custom-table tbody td {
            padding: 10px 12px;
            font-size: 12px;
        }
    }
</style>

<div class="table-container" id="widget-container-{{ $widget_id }}">
    <div class="table-title">{{$table_title}}</div>
    
    <div class="widget-actions">
        <button class="btn-toggle-details" id="toggle-btn-{{ $widget_id }}" onclick="toggleWidgetView('{{ $widget_id }}')"
                @if(isset($show_table_first) && $show_table_first)
                style="border-color: #4f46e5; color: #4f46e5; background: rgba(79, 70, 229, 0.05);"
                @endif>
            @if(isset($show_table_first) && $show_table_first)
                <i class="voyager-pie-chart"></i> <span>Show Chart View</span>
            @else
                <i class="voyager-list"></i> <span>Show Table Details</span>
            @endif
        </button>
    </div>

    <!-- Chart View Panel -->
    <div class="chart-view-wrapper" id="chart-view-{{ $widget_id }}" style="padding: 24px; background: #ffffff; @if(isset($show_table_first) && $show_table_first) display: none; @endif">
        <canvas id="canvas-{{ $widget_id }}" style="max-height: 260px; min-height: 200px; width: 100%;"></canvas>
    </div>

    <!-- Table View Panel -->
    <div class="table-view-wrapper" id="table-view-{{ $widget_id }}" style="@if(isset($show_table_first) && $show_table_first) display: block; @else display: none; @endif">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        @foreach($table_th as $th)
                            <th>{{$th}}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @if($table_data && count($table_data) > 0)
                        @foreach($table_data as $tr)
                            <tr>
                                @foreach($tr as $key => $td)
                                    <td>
                                        @php
                                            $is_file = false;
                                            $file_url = '';
                                            $file_name = '';
                                            $file_ext = '';
                                            
                                            if (is_string($td) && !empty($td)) {
                                                // Check if it's a Voyager file upload JSON
                                                if (strpos($td, '[{') === 0) {
                                                    $decoded = json_decode($td, true);
                                                    if (is_array($decoded) && isset($decoded[0]['download_link'])) {
                                                        $path = str_replace('\\', '/', $decoded[0]['download_link']);
                                                        if (Storage::disk(config('voyager.storage.disk', 'public'))->exists($path)) {
                                                            $is_file = true;
                                                            $file_url = Storage::disk(config('voyager.storage.disk', 'public'))->url($path);
                                                            $file_name = $decoded[0]['original_name'] ?? 'File';
                                                            $file_ext = strtolower(pathinfo($decoded[0]['download_link'], PATHINFO_EXTENSION));
                                                        }
                                                    }
                                                } elseif ($key === 'file_path' || $key === 'file' || $key === 'image') {
                                                    // Check if it's a direct path / URL
                                                    $ext = strtolower(pathinfo($td, PATHINFO_EXTENSION));
                                                    if (in_array($ext, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'doc', 'docx', 'xls', 'xlsx'])) {
                                                        $path = str_replace('\\', '/', $td);
                                                        $is_url = filter_var($td, FILTER_VALIDATE_URL);
                                                        if ($is_url || Storage::disk(config('voyager.storage.disk', 'public'))->exists($path)) {
                                                            $is_file = true;
                                                            $file_url = $is_url ? $td : Storage::disk(config('voyager.storage.disk', 'public'))->url($path);
                                                            $file_name = basename($td);
                                                            $file_ext = $ext;
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        @if($is_file)
                                            <button type="button" class="btn btn-xs btn-primary btn-preview-file" 
                                                    data-toggle="modal" 
                                                    data-target="#filePreviewModal-{{ $widget_id }}"
                                                    data-url="{{ $file_url }}" 
                                                    data-name="{{ $file_name }}" 
                                                    data-ext="{{ $file_ext }}"
                                                    style="border-radius: 4px; padding: 4px 10px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; border: none; background: #22A7F0; transition: background 0.15s ease;">
                                                <i class="voyager-eye"></i> View File
                                            </button>
                                        @else
                                            {{$td}}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @else
                        <tr>
                             <td colspan="{{count($table_th)}}" style="text-align: center; color: #94a3b8; padding: 24px;">No Data Found</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Premium File Preview Modal -->
<div class="modal fade" id="filePreviewModal-{{ $widget_id }}" tabindex="-1" role="dialog" aria-labelledby="filePreviewModalLabel-{{ $widget_id }}" aria-hidden="true" style="z-index: 1050;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background: {{ config('voyager.primary_color', '#22A7F0') }}; color: white; border-bottom: none; padding: 16px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8; font-size: 24px; margin-top: -2px; background: transparent; border: 0; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="filePreviewModalLabel-{{ $widget_id }}" style="font-weight: 600; font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="voyager-file-text"></i> <span class="modal-file-title-{{ $widget_id }}">File Preview</span>
                </h4>
            </div>
            <div class="modal-body" style="padding: 0; background: #f8fafc; display: flex; justify-content: center; align-items: center; min-height: 250px; position: relative;">
                <!-- Content will be dynamically appended here -->
                <div class="preview-content-placeholder-{{ $widget_id }}" style="display: none; padding: 40px; text-align: center; color: #64748b; width: 100%;">
                    <i class="voyager-file-text" style="font-size: 48px; display: block; margin-bottom: 12px; color: #94a3b8;"></i>
                    <p style="margin: 0; font-weight: 600; font-size: 14px;">Preview not available for this file type.</p>
                    <p style="margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;">Please use the download button below to view the file.</p>
                </div>
                <div class="preview-loading-{{ $widget_id }}" style="padding: 40px; text-align: center; color: {{ config('voyager.primary_color', '#22A7F0') }}; width: 100%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
                    <i class="voyager-refresh fa-spin" style="font-size: 32px; display: block; margin-bottom: 12px;"></i>
                    <p style="margin: 0; font-weight: 500;">Loading preview...</p>
                </div>
            </div>
            <div class="modal-footer" style="background: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
                <a href="#" class="btn btn-sm btn-primary btn-download-{{ $widget_id }}" download style="border-radius: 6px; font-weight: 600; padding: 6px 16px; margin: 0; display: inline-flex; align-items: center; gap: 6px; background: {{ config('voyager.primary_color', '#22A7F0') }}; border: none;">
                    <i class="voyager-download"></i> Download File
                </a>
                <button type="button" class="btn btn-sm btn-default" data-dismiss="modal" style="border-radius: 6px; font-weight: 600; padding: 6px 16px; margin: 0; border: 1px solid #cbd5e1; background: white; color: #475569;">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        (function() {
            const widgetId = '{{ $widget_id }}';
            const rawTh = @json($table_th);
            const rawData = @json($table_data);
            const title = '{{ $table_title }}';
            const requestedChartType = '{{ $chart_type ?? "" }}';

            if (!rawData || rawData.length === 0) {
                const canvas = document.getElementById(`canvas-${widgetId}`);
                if (canvas) {
                    canvas.parentNode.innerHTML = `<div style="text-align: center; color: #94a3b8; padding: 40px 0;">No active records found.</div>`;
                }
                return;
            }

            let labels = [];
            let values = [];
            let isNumerical = false;
            let numericalColIdx = -1;
            let timeColIdx = -1;

            const firstRow = rawData[0];
            const keys = Object.keys(firstRow);

            // 1. Group data based on requested chart type
            if (requestedChartType === 'line') {
                // Find date/time/day column index
                let dateColIdx = rawTh.findIndex(header => 
                    header.toLowerCase().includes('date') || 
                    header.toLowerCase().includes('day') || 
                    header.toLowerCase().includes('time')
                );
                let groupColIdx = dateColIdx !== -1 ? dateColIdx : 0;
                
                let frequencies = {};
                rawData.forEach(row => {
                    const k = Object.keys(row);
                    const val = row[k[groupColIdx]] || 'Unknown';
                    frequencies[val] = (frequencies[val] || 0) + 1;
                });
                
                // Sort keys alphabetically/chronologically
                labels = Object.keys(frequencies).sort();
                values = labels.map(lbl => frequencies[lbl]);
            } else if (requestedChartType === 'pie' || requestedChartType === 'doughnut') {
                // Find group name or teacher name or first column index
                let groupColIdx = rawTh.findIndex(header => 
                    header.toLowerCase().includes('group') || 
                    header.toLowerCase().includes('class')
                );
                let teacherColIdx = rawTh.findIndex(header => 
                    header.toLowerCase().includes('teacher') || 
                    header.toLowerCase().includes('name')
                );
                let groupIdx = groupColIdx !== -1 ? groupColIdx : (teacherColIdx !== -1 ? teacherColIdx : 0);
                
                let frequencies = {};
                rawData.forEach(row => {
                    const k = Object.keys(row);
                    const val = row[k[groupIdx]] || 'Unknown';
                    frequencies[val] = (frequencies[val] || 0) + 1;
                });
                
                labels = Object.keys(frequencies);
                values = Object.values(frequencies);
            } else {
                // Default logic
                if (rawTh.length > 1) {
                    for (let i = 1; i < keys.length; i++) {
                        const val = firstRow[keys[i]];
                        if (val !== null && val !== undefined && val !== '' && !isNaN(parseFloat(val)) && isFinite(val)) {
                            isNumerical = true;
                            numericalColIdx = i;
                            break;
                        }
                    }
                }

                if (!isNumerical) {
                    const timeKeywords = ['date', 'time', 'day', 'schedule', 'week', 'hour', 'year', 'month'];
                    for (let i = 0; i < rawTh.length; i++) {
                        const header = rawTh[i].toLowerCase();
                        if (timeKeywords.some(keyword => header.includes(keyword))) {
                            timeColIdx = i;
                            break;
                        }
                    }
                }

                if (isNumerical && numericalColIdx !== -1) {
                    rawData.forEach(row => {
                        const labelVal = row[keys[0]] || 'Unknown';
                        const numericVal = parseFloat(row[keys[numericalColIdx]]) || 0;
                        labels.push(labelVal);
                        values.push(numericVal);
                    });
                } else if (timeColIdx !== -1) {
                    let frequencies = {};
                    rawData.forEach(row => {
                        const labelVal = row[keys[timeColIdx]] || 'Unknown';
                        frequencies[labelVal] = (frequencies[labelVal] || 0) + 1;
                    });
                    labels = Object.keys(frequencies).sort();
                    values = labels.map(lbl => frequencies[lbl]);
                } else {
                    let frequencies = {};
                    rawData.forEach(row => {
                        const labelVal = row[keys[0]] || 'Other';
                        frequencies[labelVal] = (frequencies[labelVal] || 0) + 1;
                    });
                    labels = Object.keys(frequencies);
                    values = Object.values(frequencies);
                }
            }

            const canvas = document.getElementById(`canvas-${widgetId}`);
            if (!canvas) return;

            if (labels.length === 0) {
                canvas.parentNode.innerHTML = `<div style="text-align: center; color: #94a3b8; padding: 40px 0;">No graphic data mapping available.</div>`;
                return;
            }

            // 2. Select Chart style
            let chartType = requestedChartType || (isNumerical ? 'bar' : 'horizontalBar');
            const datasetLabel = isNumerical && numericalColIdx !== -1 ? rawTh[numericalColIdx] : 'Items';

            const primaryColor = "{{ config('voyager.primary_color', '#22A7F0') }}";

            function hexToRgba(hex, alpha) {
                let r = 0, g = 0, b = 0;
                if (hex.length === 4) {
                    r = parseInt(hex[1] + hex[1], 16);
                    g = parseInt(hex[2] + hex[2], 16);
                    b = parseInt(hex[3] + hex[3], 16);
                } else if (hex.length === 7) {
                    r = parseInt(hex.slice(1, 3), 16);
                    g = parseInt(hex.slice(3, 5), 16);
                    b = parseInt(hex.slice(5, 7), 16);
                }
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }

            const themeColors = [
                primaryColor,
                '#3b82f6', // Blue
                '#10b981', // Emerald
                '#0ea5e9', // Cyan
                '#f59e0b', // Amber
                '#ec4899', // Pink
                '#8b5cf6', // Violet
                '#f43f5e', // Rose
                '#14b8a6', // Teal
                '#f97316'  // Orange
            ];

            const bgArray = labels.map((_, index) => {
                const color = (chartType === 'pie' || chartType === 'doughnut')
                    ? themeColors[index % themeColors.length]
                    : primaryColor;
                return hexToRgba(color, 0.75);
            });

            const borderArray = labels.map((_, index) => {
                return (chartType === 'pie' || chartType === 'doughnut')
                    ? themeColors[index % themeColors.length]
                    : primaryColor;
            });

            let datasetConfig = {
                label: datasetLabel,
                data: values,
                backgroundColor: bgArray,
                borderColor: borderArray,
                borderWidth: 1.5,
                borderRadius: 6
            };

            if (chartType === 'line') {
                datasetConfig = {
                    label: datasetLabel,
                    data: values,
                    fill: true,
                    backgroundColor: hexToRgba(primaryColor, 0.15),
                    borderColor: primaryColor,
                    borderWidth: 2.5,
                    lineTension: 0.3,
                    pointBackgroundColor: primaryColor,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointHitRadius: 10
                };
            }

            const ctx = canvas.getContext('2d');
            const data = {
                labels: labels,
                datasets: [datasetConfig]
            };

            let options = {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: (chartType === 'pie' || chartType === 'doughnut') ? true : false,
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        fontSize: 10,
                        fontColor: '#64748b',
                        padding: 10
                    }
                },
                tooltips: {
                    backgroundColor: '#0f172a',
                    titleFontSize: 11,
                    bodyFontSize: 11,
                    xPadding: 10,
                    yPadding: 10,
                    cornerRadius: 8,
                    displayColors: (chartType === 'pie' || chartType === 'doughnut') ? true : false
                }
            };

            if (chartType !== 'pie' && chartType !== 'doughnut') {
                options.scales = {
                    yAxes: [{
                        gridLines: {
                            color: 'rgba(226, 232, 240, 0.5)',
                            zeroLineColor: 'rgba(226, 232, 240, 0.8)'
                        },
                        ticks: {
                            beginAtZero: true,
                            fontSize: 10,
                            fontColor: '#64748b'
                        }
                    }],
                    xAxes: [{
                        gridLines: {
                            display: false
                        },
                        ticks: {
                            fontSize: 10,
                            fontColor: '#64748b'
                        }
                    }]
                };

                if (chartType === 'horizontalBar') {
                    options.scales = {
                        xAxes: [{
                            gridLines: {
                                color: 'rgba(226, 232, 240, 0.5)',
                                zeroLineColor: 'rgba(226, 232, 240, 0.8)'
                            },
                            ticks: {
                                beginAtZero: true,
                                fontSize: 10,
                                fontColor: '#64748b',
                                stepSize: 1
                            }
                        }],
                        yAxes: [{
                            gridLines: {
                                display: false
                            },
                            ticks: {
                                fontSize: 10,
                                fontColor: '#64748b'
                            }
                        }]
                    };
                }
            }

            let chartCreated = false;
            if (window.Chart) {
                try {
                    new Chart(ctx, {
                        type: chartType,
                        data: data,
                        options: options
                    });
                    chartCreated = true;
                } catch (e) {
                    console.error("Chart creation failed: ", e);
                }
            }

            // Fallback: if chart wasn't created (e.g. Chart.js failed to load), show table view
            if (!chartCreated) {
                const showTableFirst = {{ (isset($show_table_first) && $show_table_first) ? 'true' : 'false' }};
                if (!showTableFirst) {
                    toggleWidgetView(widgetId);
                }
            }
        })();
    });

    if (typeof window.toggleWidgetView === 'undefined') {
        window.toggleWidgetView = function(widgetId) {
            const tableView = document.getElementById(`table-view-${widgetId}`);
            const chartView = document.getElementById(`chart-view-${widgetId}`);
            const toggleBtn = document.getElementById(`toggle-btn-${widgetId}`);
            
            if (tableView.style.display === 'none') {
                tableView.style.display = 'block';
                chartView.style.display = 'none';
                toggleBtn.innerHTML = '<i class="voyager-pie-chart"></i> <span>Show Chart View</span>';
                toggleBtn.style.borderColor = '#4f46e5';
                toggleBtn.style.color = '#4f46e5';
                toggleBtn.style.background = 'rgba(79, 70, 229, 0.05)';
            } else {
                tableView.style.display = 'none';
                chartView.style.display = 'block';
                toggleBtn.innerHTML = '<i class="voyager-list"></i> <span>Show Table Details</span>';
                toggleBtn.style.borderColor = '#cbd5e1';
                toggleBtn.style.color = '#475569';
                toggleBtn.style.background = '#ffffff';
            }
        }
    }

    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function($) {
            $('#filePreviewModal-{{ $widget_id }}').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var fileUrl = button.data('url');
                var fileName = button.data('name');
                var fileExt = button.data('ext');
                
                var modal = $(this);
                modal.find('.modal-file-title-{{ $widget_id }}').text(fileName);
                modal.find('.btn-download-{{ $widget_id }}').attr('href', fileUrl).attr('download', fileName);
                
                var body = modal.find('.modal-body');
                body.find('.dynamic-preview').remove();
                
                var loading = modal.find('.preview-loading-{{ $widget_id }}');
                var placeholder = modal.find('.preview-content-placeholder-{{ $widget_id }}');
                
                loading.show();
                placeholder.hide();
                
                var contentHtml = '';
                
                if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].indexOf(fileExt) !== -1) {
                    contentHtml = '<img class="dynamic-preview" src="' + fileUrl + '" style="max-width: 90%; max-height: 70vh; object-fit: contain; border-radius: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin: 20px; display: none;" />';
                } else if (fileExt === 'pdf') {
                    contentHtml = '<iframe class="dynamic-preview" src="' + fileUrl + '" style="width: 100%; height: 70vh; border: none; border-radius: 0; display: none;"></iframe>';
                } else {
                    placeholder.show();
                    loading.hide();
                }
                
                if (contentHtml !== '') {
                    var $content = $(contentHtml);
                    body.append($content);
                    
                    $content.on('load', function() {
                        loading.hide();
                        $content.show();
                    });
                    
                    // Fallback in case load event does not fire (e.g. cached/preloaded content)
                    setTimeout(function() {
                        loading.hide();
                        $content.show();
                    }, 1000);
                }
            });
            
            $('#filePreviewModal-{{ $widget_id }}').on('hidden.bs.modal', function () {
                $(this).find('.modal-body .dynamic-preview').remove();
            });
        });
    }
</script>

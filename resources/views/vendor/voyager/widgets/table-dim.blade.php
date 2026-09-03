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
        <button class="btn-toggle-details" id="toggle-btn-{{ $widget_id }}" onclick="toggleWidgetView('{{ $widget_id }}')">
            <i class="voyager-list"></i> <span>Show Table Details</span>
        </button>
    </div>

    <!-- Chart View Panel -->
    <div class="chart-view-wrapper" id="chart-view-{{ $widget_id }}" style="padding: 24px; background: #ffffff;">
        <canvas id="canvas-{{ $widget_id }}" style="max-height: 260px; min-height: 200px; width: 100%;"></canvas>
    </div>

    <!-- Table View Panel (Hidden by default) -->
    <div class="table-view-wrapper" id="table-view-{{ $widget_id }}" style="display: none;">
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
                                @foreach($tr as $td)
                                    <td>{{$td}}</td>
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
                toggleWidgetView(widgetId);
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
</script>

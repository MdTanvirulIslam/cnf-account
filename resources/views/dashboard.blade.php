@extends('layouts.layout')

@section('content')


    <div class="row layout-top-spacing">
        <!--  BEGIN DASHBOARD CONTENT  -->
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
            <div class="widget widget-card-four">
                <div class="widget-content">
                    <div class="w-header">
                        <div class="w-info">
                            <h6 class="value">Total Receive (Jan-Dec)</h6>
                        </div>

                    </div>

                    <div class="w-content">

                        <div class="w-info">
                            <p class="value">{{ $total_received }} <span>this year</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="feather feather-trending-up">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                </svg>
                            </p>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
            <div class="widget widget-card-four">
                <div class="widget-content">
                    <div class="w-header">
                        <div class="w-info">
                            <h6 class="value">Total Expenses (Jan-Dec)</h6>
                        </div>

                    </div>


                    <div class="w-content">

                        <div class="w-info">
                            <p class="value">{{ $total_expense }} <span>this year</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="feather feather-trending-up">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                </svg>
                            </p>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
            <div class="widget widget-card-four">
                <div class="widget-content">
                    <div class="w-header">
                        <div class="w-info">
                            <h6 class="value">Total Receive</h6>
                        </div>

                    </div>


                    <div class="w-content">

                        <div class="w-info">
                            <p class="value">{{ $this_month_receive }} <span>this month</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="feather feather-trending-up">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                </svg>
                            </p>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
            <div class="widget widget-chart-three">
                <div class="widget-heading">
                    <div class="">
                        <h5 class="">Month wise Yearly Expense</h5>
                    </div>

                    <div class="task-action">
                        <div class="dropdown ">
                            <a class="dropdown-toggle" href="#" role="button" id="uniqueVisitors"
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="feather feather-more-horizontal">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="19" cy="12" r="1"></circle>
                                    <circle cx="5" cy="12" r="1"></circle>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="widget-content">
                    <div id="uniqueVisits"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
            <div class="widget-four">
                <div class="widget-heading">
                    <h5 class="">Total Individual Cost</h5>
                    <div class="widget-heading-right">
                        <span class="badge badge-info">Total: {{ number_format($individual_cost['total_all_costs'], 2) }}</span>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="vistorsBrowser">
                        <div class="browser-list">
                            <div class="w-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <text x="12" y="17" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" font-weight="bold">৳</text>
                                </svg>
                            </div>
                            <div class="w-browser-details">
                                <div class="w-browser-info">
                                    <h6>Total Export Cost</h6>
                                    <p class="browser-count">{{ number_format($individual_cost['total_export_cost'], 2) }}</p>
                                    <small class="text-muted">{{ $individual_cost['export_percentage'] }}% of total</small>
                                </div>
                                <div class="w-browser-stats">
                                    <div class="progress">
                                        <div class="progress-bar bg-gradient-primary" role="progressbar"
                                             style="width: {{ $individual_cost['export_percentage'] }}%"
                                             aria-valuenow="{{ $individual_cost['export_percentage'] }}"
                                             aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="browser-list">
                            <div class="w-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <text x="12" y="17" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" font-weight="bold">৳</text>
                                </svg>
                            </div>
                            <div class="w-browser-details">
                                <div class="w-browser-info">
                                    <h6>Total Import Cost</h6>
                                    <p class="browser-count">{{ number_format($individual_cost['total_import_cost'], 2) }}</p>
                                    <small class="text-muted">{{ $individual_cost['import_percentage'] }}% of total</small>
                                </div>
                                <div class="w-browser-stats">
                                    <div class="progress">
                                        <div class="progress-bar bg-gradient-danger" role="progressbar"
                                             style="width: {{ $individual_cost['import_percentage'] }}%"
                                             aria-valuenow="{{ $individual_cost['import_percentage'] }}"
                                             aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="browser-list">
                            <div class="w-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <text x="12" y="17" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" font-weight="bold">৳</text>
                                </svg>
                            </div>
                            <div class="w-browser-details">
                                <div class="w-browser-info">
                                    <h6>Total Office Expense</h6>
                                    <p class="browser-count">{{ number_format($individual_cost['office_expense'], 2) }}</p>
                                    <small class="text-muted">{{ $individual_cost['office_percentage'] }}% of total</small>
                                </div>
                                <div class="w-browser-stats">
                                    <div class="progress">
                                        <div class="progress-bar bg-gradient-warning" role="progressbar"
                                             style="width: {{ $individual_cost['office_percentage'] }}%"
                                             aria-valuenow="{{ $individual_cost['office_percentage'] }}"
                                             aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="row widget-statistic">
                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12 layout-spacing">
                    <div class="widget widget-one_hybrid widget-followers">
                        <div class="widget-heading">
                            <div class="w-title">
                                <div class="w-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/>
                                        <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/>
                                        <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>
                                    </svg>
                                </div>
                                <div class="">
                                    <p class="w-value">{{ $this_month_sonali_receive }}</p>
                                    <h5 class="">This Month Sonali Bank Receive</h5>
                                </div>
                            </div>
                        </div>
                        <div class="widget-content">
                            <div class="w-chart">
                                <div id="hybrid_followers"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12 layout-spacing">
                    <div class="widget widget-one_hybrid widget-referral">
                        <div class="widget-heading">
                            <div class="w-title">
                                <div class="w-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/>
                                        <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/>
                                        <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>
                                    </svg>
                                </div>
                                <div class="">
                                    <p class="w-value"> {{ $this_moth_janata_receive }}</p>
                                    <h5 class="">This Month Janata Bank Receive</h5>
                                </div>
                            </div>
                        </div>
                        <div class="widget-content">
                            <div class="w-chart">
                                <div id="hybrid_followers1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--  END DASHBOARD CONTENT  -->


    </div>

@endsection

@section('scripts')

        <script>
            // Initialize chart with empty data first
            var d_1options1 = {
            chart: {
            height: 350,
            type: 'bar',
            toolbar: {
            show: false,
        }
        },
            colors: ['#622bd7', '#ffbb44','#622b88'],
            plotOptions: {
            bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded',
            borderRadius: 10,
        },
        },
            dataLabels: {
            enabled: false
        },
            legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            fontSize: '14px',
            markers: {
            width: 10,
            height: 10,
            offsetX: -5,
            offsetY: 0
        },
            itemMargin: {
            horizontal: 10,
            vertical: 8
        }
        },
            grid: {
            borderColor: '#e0e6ed',
        },
            stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
            series: [
        {
            name: 'Import Expense',
            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] // Placeholder
        },
        {
            name: 'Export Expense',
            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] // Placeholder
        },
        {
            name: 'Office Expense',
            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] // Placeholder
        }
            ],
            xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        },
            fill: {
            type: 'gradient',
            gradient: {
            shade: 'light',
            type: 'vertical',
            shadeIntensity: 0.3,
            inverseColors: false,
            opacityFrom: 1,
            opacityTo: 0.8,
            stops: [0, 100]
        }
        },
            tooltip: {
            marker : {
            show: false,
        },
            theme: 'light',
            y: {
            formatter: function (val) {
            return '৳' + val.toLocaleString() // Format as currency
        }
        }
        },
            responsive: [
        {
            breakpoint: 767,
            options: {
            plotOptions: {
            bar: {
            borderRadius: 0,
            columnWidth: "50%"
        }
        }
        }
        },
            ]
        };

            // Create chart instance
            var chart = new ApexCharts(document.querySelector("#uniqueVisits"), d_1options1);
            chart.render();

            // Function to fetch and update chart data
            function loadChartData() {
            fetch('{{ route("chart.data") }}')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Chart data loaded:', data); // For debugging

                    // Update chart series with dynamic data
                    chart.updateSeries([
                        {
                            name: 'Import Expense',
                            data: data.import
                        },
                        {
                            name: 'Export Expense',
                            data: data.export
                        },
                        {
                            name: 'Office Expense',
                            data: data.office
                        }
                    ]);
                })
                .catch(error => {
                    console.error('Error loading chart data:', error);
                    // Show error message to user
                    alert('Failed to load chart data. Please try again.');
                });
        }

            // Load data when page is ready
            document.addEventListener('DOMContentLoaded', function() {
            loadChartData();
        });

            // Optional: Add loading indicator
            function showChartLoading() {
            document.querySelector("#uniqueVisits").innerHTML = '<div class="text-center p-4">Loading chart data...</div>';
        }

            function hideChartLoading() {
            // Loading will be hidden when chart renders
        }
    </script>

@endsection

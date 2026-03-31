// window.initMyChart = function(chartdata) {
//  var chartdata = JSON.parse(document.getElementById('myChartData').getAttribute('data-chart'));

//     // Use chartdata to feed your chart
//     console.log(chartdata);
//     // ... your chart code here ...
// };

const chartData = JSON.parse(document.getElementById('myChartData').getAttribute('data-chart')) ?? [];
console.log(chartData);

$(document).ready(function () {
    // Activity Chart (Area Chart)
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: chartData.map(data=>data.month.slice(0,3)),
            datasets: [{
                data: chartData.map(data=>data.students),
                borderColor: '#3b82f6',
                backgroundColor: function (context) {
                    const chart = context.chart;
                    const { ctx, chartArea } = chart;
                    if (!chartArea) {
                        return null;
                    }
                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
                    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.05)');
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    
                    ticks: {
                        callback: function (value) {
                            return value.toLocaleString() + ' Students';
                        },
                        stepSize: 1000
                    },
                    grid: {
                        color: '#f3f4f6'
                    },
                    border: {
                        display: false
                    }
                }
            },
            elements: {
                point: {
                    hoverBackgroundColor: '#3b82f6'
                }
            }
        }
    });

    // Progress Chart (Bar Chart)
    const progressCtx = document.getElementById('progressChart').getContext('2d');
    const progressChart = new Chart(progressCtx, {
        type: 'bar',
        data: {
            labels: ['Kigali', 'Bugesera', 'Rulreca', 'Nyaning'],
            datasets: [{
                data: [38, 42, 35, 34],
                backgroundColor: '#3b82f6',
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    max: 50,
                    ticks: {
                        stepSize: 10
                    },
                    grid: {
                        color: '#f3f4f6'
                    },
                    border: {
                        display: false
                    }
                }
            }
        }
    });

    // District Chart 1 (Bar Chart)
    const district1Ctx = document.getElementById('districtChart1').getContext('2d');
    const district1Chart = new Chart(district1Ctx, {
        type: 'bar',
        data: {
            labels: ['Kigali', 'City', 'Bugesera', 'Gakenko', 'Burera'],
            datasets: [{
                data: [850, 950, 1100, 1200, 1350, 1450, 1550],
                backgroundColor: '#3b82f6',
                borderRadius: 6,
                borderSkipped: false,
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    max: 1600,
                    ticks: {
                        stepSize: 400,
                        callback: function (value) {
                            return value;
                        }
                    },
                    grid: {
                        color: '#f3f4f6'
                    },
                    border: {
                        display: false
                    }
                }
            }
        }
    });

    // District Chart 2 (Bar Chart)
    const district2Ctx = document.getElementById('districtChart2').getContext('2d');
    const district2Chart = new Chart(district2Ctx, {
        type: 'bar',
        data: {
            labels: ['Kigali', 'Bugesera', 'Burera', 'Nyolhu', 'Ruisiro', 'Nyanza'],
            datasets: [{
                data: [120000, 90000, 80000, 70000, 65000, 60000],
                backgroundColor: '#3b82f6',
                borderRadius: 6,
                borderSkipped: false,
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    max: 150000,
                    ticks: {
                        stepSize: 50000,
                        callback: function (value) {
                            return (value / 1000) + 'k';
                        }
                    },
                    grid: {
                        color: '#f3f4f6'
                    },
                    border: {
                        display: false
                    }
                }
            }
        }
    });

    // Month dropdown functionality
    $('.month-dropdown').on('change', function () {
        const selectedMonth = $(this).val();
        console.log('Selected month:', selectedMonth);
        // You can add functionality to update charts based on selected month
    });
});
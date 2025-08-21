<div>
    <div class="chart-container" style="position: relative; height:600px; width:100%">
        <canvas id="{{ $chartId }}"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function () {
            const ctx = document.getElementById('{{ $chartId }}').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($labels),
                    datasets: [
                        {
                            label: 'Cantidad Planificada',
                            data: @json($data[0] ?? []),
                            backgroundColor: 'rgba(21, 93, 252, 0.2)',
                            borderColor: 'rgba(25, 60, 184, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            barThickness: 8
                        },
                        {
                            label: 'Cantidad Producida',
                            data: @json($data[1] ?? []),
                            backgroundColor: 'rgba(0, 153, 102, 0.2)',
                            borderColor: 'rgba(0, 96, 69, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            barThickness: 8
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad'
                            }
                        },
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.x;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</div>

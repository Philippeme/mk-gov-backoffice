// MK Gov Backoffice - Dashboard JavaScript
// Handles dashboard interactions, charts, and real-time updates

class Dashboard {
    constructor() {
        this.charts = {};
        this.realTimeEnabled = true;
        this.refreshInterval = null;
        this.init();
    }

    init() {
        this.initializeCharts();
        this.setupEventListeners();
        this.startRealTimeUpdates();
        this.initializeMap();
    }

    // Initialize all dashboard charts
    initializeCharts() {
        this.initializeBirthRateChart();
        this.initializeMissingBirthsChart();
        this.initializePendingByAdminChart();
        this.initializePendingByCitizensChart();
        this.initializePerformanceMetrics();
    }

    initializeBirthRateChart() {
        const ctx = document.getElementById('birthRateChart');
        if (!ctx) return;

        this.charts.birthRate = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Birth Declarations %',
                    data: [23.26, 25.1, 22.8, 28.3, 24.7, 26.5, 25.9, 27.2, 23.8, 25.4, 24.1, 26.8],
                    borderColor: 'rgb(255, 159, 64)',
                    backgroundColor: 'rgba(255, 159, 64, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: 'rgb(255, 159, 64)',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgb(255, 159, 64)',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        display: true,
                        beginAtZero: true,
                        max: 35,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }

    initializeMissingBirthsChart() {
        const ctx = document.getElementById('missingBirthsChart');
        if (!ctx) return;

        this.charts.missingBirths = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Missing Births %',
                    data: [16.98, 15.2, 18.4, 14.7, 17.3, 16.1, 15.8, 17.9, 16.5, 15.3, 17.8, 16.2],
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        display: true,
                        beginAtZero: true,
                        max: 20,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    initializePendingByAdminChart() {
        const ctx = document.getElementById('pendingByAdminChart');
        if (!ctx) return;

        this.charts.pendingByAdmin = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['DGSN', 'Ministry of Justice', 'Civil Registry', 'Prefecture'],
                datasets: [{
                    data: [45, 23, 67, 12],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 2,
                    hoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed * 100) / total).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    initializePendingByCitizensChart() {
        const ctx = document.getElementById('pendingByCitizensChart');
        if (!ctx) return;

        this.charts.pendingByCitizens = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Missing Documents', 'Payment Pending', 'Information Required', 'Appointment Needed'],
                datasets: [{
                    data: [89, 34, 56, 23],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 2,
                    hoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed * 100) / total).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    initializePerformanceMetrics() {
        // Animate performance bars
        const performanceBars = document.querySelectorAll('.progress-bar');
        performanceBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.transition = 'width 1s ease-in-out';
                bar.style.width = width;
            }, 500);
        });
    }

    // Initialize interactive map
    initializeMap() {
        const mapContainer = document.getElementById('cameroonMap');
        if (!mapContainer) return;

        // Initialize Leaflet map
        this.map = L.map('cameroonMap').setView([7.3697, 12.3547], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        // Add custom controls
        this.addMapControls();
        
        // Load and display regions data
        this.loadRegionsData();
    }

    addMapControls() {
        // Add filter controls to map
        const filterControl = L.control({ position: 'topright' });
        
        filterControl.onAdd = function() {
            const div = L.DomUtil.create('div', 'map-filter-control');
            div.innerHTML = `
                <div class="map-filters">
                    <select id="mapRegionFilter" class="form-select form-select-sm">
                        <option value="all">All Regions</option>
                        <option value="centre">Centre</option>
                        <option value="littoral">Littoral</option>
                        <option value="nord">Nord</option>
                        <option value="ouest">Ouest</option>
                        <option value="sud">Sud</option>
                    </select>
                </div>
            `;
            
            L.DomEvent.disableClickPropagation(div);
            return div;
        };
        
        filterControl.addTo(this.map);

        // Add legend
        const legend = L.control({ position: 'bottomright' });
        
        legend.onAdd = function() {
            const div = L.DomUtil.create('div', 'map-legend');
            div.innerHTML = `
                <div class="legend-content">
                    <h6>Request Volume</h6>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #2196f3;"></span>
                        <span>High (1000+)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #4caf50;"></span>
                        <span>Medium (500-999)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #ff9800;"></span>
                        <span>Low (< 500)</span>
                    </div>
                </div>
            `;
            return div;
        };
        
        legend.addTo(this.map);
    }

    loadRegionsData() {
        fetch('/admin/dashboard/api/map-data')
            .then(response => response.json())
            .then(data => {
                this.displayRegionsOnMap(data.regions);
            })
            .catch(error => {
                console.error('Error loading map data:', error);
            });
    }

    displayRegionsOnMap(regions) {
        regions.forEach(region => {
            const color = this.getRegionColor(region.statistics.total_requests);
            
            const marker = L.circleMarker(region.coordinates, {
                radius: this.getMarkerSize(region.statistics.total_requests),
                fillColor: color,
                color: '#fff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(this.map);

            const popupContent = `
                <div class="region-popup">
                    <h6>${region.name}</h6>
                    <div class="popup-stats">
                        <div class="stat-item">
                            <span class="label">Total Requests:</span>
                            <span class="value">${region.statistics.total_requests.toLocaleString()}</span>
                        </div>
                        <div class="stat-item">
                            <span class="label">Completed:</span>
                            <span class="value">${region.statistics.completed.toLocaleString()}</span>
                        </div>
                        <div class="stat-item">
                            <span class="label">Pending:</span>
                            <span class="value">${region.statistics.pending.toLocaleString()}</span>
                        </div>
                        <div class="stat-item">
                            <span class="label">Population:</span>
                            <span class="value">${region.statistics.population.toLocaleString()}</span>
                        </div>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);
        });
    }

    getRegionColor(requestCount) {
        if (requestCount >= 1000) return '#2196f3';
        if (requestCount >= 500) return '#4caf50';
        return '#ff9800';
    }

    getMarkerSize(requestCount) {
        if (requestCount >= 2000) return 20;
        if (requestCount >= 1000) return 15;
        if (requestCount >= 500) return 10;
        return 8;
    }

    // Setup event listeners
    setupEventListeners() {
        // Filter form submission
        const filterForm = document.getElementById('dashboardFilters');
        if (filterForm) {
            filterForm.addEventListener('submit', this.handleFilterSubmit.bind(this));
        }

        // Reset filters button
        const resetBtn = document.getElementById('resetFilters');
        if (resetBtn) {
            resetBtn.addEventListener('click', this.resetFilters.bind(this));
        }

        // Refresh map button
        const refreshMapBtn = document.getElementById('refreshMap');
        if (refreshMapBtn) {
            refreshMapBtn.addEventListener('click', this.refreshMap.bind(this));
        }

        // Period selector for charts
        document.querySelectorAll('input[name="birthRatePeriod"]').forEach(radio => {
            radio.addEventListener('change', this.updateBirthRateChart.bind(this));
        });

        document.querySelectorAll('input[name="missingBirthsPeriod"]').forEach(radio => {
            radio.addEventListener('change', this.updateMissingBirthsChart.bind(this));
        });

        // Real-time toggle
        const realTimeToggle = document.getElementById('realTimeToggle');
        if (realTimeToggle) {
            realTimeToggle.addEventListener('change', this.toggleRealTime.bind(this));
        }
    }

    handleFilterSubmit(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const params = new URLSearchParams(formData);
        
        // Update URL with new filters
        const newUrl = window.location.pathname + '?' + params.toString();
        window.history.pushState({}, '', newUrl);
        
        // Refresh dashboard data
        this.refreshDashboard();
    }

    resetFilters() {
        const form = document.getElementById('dashboardFilters');
        if (form) {
            form.reset();
            window.history.pushState({}, '', window.location.pathname);
            this.refreshDashboard();
        }
    }

    refreshMap() {
        if (this.map) {
            this.loadRegionsData();
            this.showNotification('Map data refreshed', 'success');
        }
    }

    updateBirthRateChart(e) {
        const period = e.target.value;
        
        // Simulate different data for different periods
        let data, labels;
        
        if (period === '6m') {
            labels = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            data = [25.9, 27.2, 23.8, 25.4, 24.1, 26.8];
        } else {
            labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            data = [23.26, 25.1, 22.8, 28.3, 24.7, 26.5, 25.9, 27.2, 23.8, 25.4, 24.1, 26.8];
        }
        
        this.charts.birthRate.data.labels = labels;
        this.charts.birthRate.data.datasets[0].data = data;
        this.charts.birthRate.update('active');
    }

    updateMissingBirthsChart(e) {
        const period = e.target.value;
        
        let data, labels;
        
        if (period === '6m') {
            labels = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            data = [15.8, 17.9, 16.5, 15.3, 17.8, 16.2];
        } else {
            labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            data = [16.98, 15.2, 18.4, 14.7, 17.3, 16.1, 15.8, 17.9, 16.5, 15.3, 17.8, 16.2];
        }
        
        this.charts.missingBirths.data.labels = labels;
        this.charts.missingBirths.data.datasets[0].data = data;
        this.charts.missingBirths.update('active');
    }

    // Real-time updates
    toggleRealTime(e) {
        this.realTimeEnabled = e.target.checked;
        
        if (this.realTimeEnabled) {
            this.startRealTimeUpdates();
            this.showNotification('Real-time updates enabled', 'success');
        } else {
            this.stopRealTimeUpdates();
            this.showNotification('Real-time updates disabled', 'info');
        }
    }

    startRealTimeUpdates() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        
        this.refreshInterval = setInterval(() => {
            if (this.realTimeEnabled) {
                this.updateStatistics();
            }
        }, 30000); // Update every 30 seconds
    }

    stopRealTimeUpdates() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    updateStatistics() {
        fetch('/admin/dashboard/api/statistics')
            .then(response => response.json())
            .then(data => {
                this.updateStatisticCards(data);
            })
            .catch(error => {
                console.error('Error updating statistics:', error);
            });
    }

    updateStatisticCards(data) {
        // Update statistic cards with new data
        const statCards = document.querySelectorAll('.stat-card');
        
        statCards.forEach(card => {
            const valueElement = card.querySelector('.stat-value');
            if (valueElement) {
                // Add animation effect
                valueElement.classList.add('updating');
                setTimeout(() => {
                    valueElement.classList.remove('updating');
                }, 500);
            }
        });
    }

    refreshDashboard() {
        // Show loading state
        this.showLoadingState();
        
        // Refresh all dashboard components
        Promise.all([
            this.updateStatistics(),
            this.loadRegionsData(),
            this.refreshCharts()
        ]).then(() => {
            this.hideLoadingState();
            this.showNotification('Dashboard refreshed', 'success');
        }).catch(error => {
            this.hideLoadingState();
            this.showNotification('Error refreshing dashboard', 'error');
            console.error('Dashboard refresh error:', error);
        });
    }

    refreshCharts() {
        // Refresh chart data
        return Promise.all([
            this.updateChartData('birthRate'),
            this.updateChartData('missingBirths')
        ]);
    }

    updateChartData(chartName) {
        return fetch(`/admin/dashboard/api/charts/${chartName}`)
            .then(response => response.json())
            .then(data => {
                if (this.charts[chartName]) {
                    this.charts[chartName].data = data;
                    this.charts[chartName].update('active');
                }
            });
    }

    showLoadingState() {
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'dashboardLoading';
        loadingOverlay.className = 'dashboard-loading';
        loadingOverlay.innerHTML = `
            <div class="loading-content">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Refreshing dashboard...</p>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
    }

    hideLoadingState() {
        const loadingOverlay = document.getElementById('dashboardLoading');
        if (loadingOverlay) {
            loadingOverlay.remove();
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show dashboard-notification`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.dashboard-container') || document.body;
        container.insertBefore(notification, container.firstChild);
        
        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    // Utility methods
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }

    // Cleanup method
    destroy() {
        this.stopRealTimeUpdates();
        
        // Destroy charts
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        
        // Remove map
        if (this.map) {
            this.map.remove();
        }
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.mkgovDashboard = new Dashboard();
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (window.mkgovDashboard) {
        window.mkgovDashboard.destroy();
    }
});
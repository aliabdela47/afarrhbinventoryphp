/**
 * Dashboard Application Script
 * Handles dashboard charts and real-time updates
 */

(function() {
    'use strict';
    
    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
        initStatsCards();
        initCharts();
        initActivityFeed();
    });
    
    function initStatsCards() {
        // Animate numbers on stats cards
        const statNumbers = document.querySelectorAll('.stats-number');
        
        statNumbers.forEach(stat => {
            const target = parseInt(stat.textContent);
            animateNumber(stat, 0, target, 1000);
        });
    }
    
    function animateNumber(element, start, end, duration) {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;
        
        const timer = setInterval(function() {
            current += increment;
            if (current >= end) {
                element.textContent = end;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 16);
    }
    
    function initCharts() {
        // Stock Levels Chart
        const stockLevelsEl = document.querySelector('#stockLevelsChart');
        if (stockLevelsEl) {
            const stockLevelsChart = new ApexCharts(stockLevelsEl, {
                chart: {
                    type: 'bar',
                    height: 300
                },
                series: [{
                    name: 'Current Stock',
                    data: [65, 45, 78, 34, 89, 56, 67, 92]
                }],
                xaxis: {
                    categories: ['Medical', 'Office', 'Equipment', 'Cleaning', 'IT', 'Furniture', 'Safety', 'Other']
                }
            });
            stockLevelsChart.render();
        }
        
        // Monthly Issuances Chart
        const monthlyIssuancesEl = document.querySelector('#monthlyIssuancesChart');
        if (monthlyIssuancesEl) {
            const monthlyIssuancesChart = new ApexCharts(monthlyIssuancesEl, {
                chart: {
                    type: 'line',
                    height: 300
                },
                series: [{
                    name: 'Issuances',
                    data: [12, 18, 15, 22, 19, 25, 21, 28, 24, 30, 27, 32]
                }],
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                }
            });
            monthlyIssuancesChart.render();
        }
        
        // Vehicle Utilization Chart
        const vehicleUtilizationEl = document.querySelector('#vehicleUtilizationChart');
        if (vehicleUtilizationEl) {
            const vehicleUtilizationChart = new ApexCharts(vehicleUtilizationEl, {
                chart: {
                    type: 'donut',
                    height: 300
                },
                series: [65, 25, 10],
                labels: ['In Use', 'Available', 'Maintenance']
            });
            vehicleUtilizationChart.render();
        }
    }
    
    function initActivityFeed() {
        // Auto-refresh activity feed every 30 seconds
        setInterval(refreshActivityFeed, 30000);
    }
    
    function refreshActivityFeed() {
        // Placeholder for AJAX call to refresh activity feed
        // In production, this would fetch latest activities from server
    }
    
})();

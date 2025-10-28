/**
 * Vehicle Tracking Application Script
 * Handles vehicle tracking map and check-in functionality
 */

(function() {
    'use strict';
    
    let map = null;
    let markers = [];
    let trackingInterval = null;
    
    // Initialize tracking page
    document.addEventListener('DOMContentLoaded', function() {
        const mapElement = document.querySelector('#trackingMap');
        if (mapElement) {
            initMap();
            loadTrackingData();
            
            // Auto-refresh every 30 seconds
            trackingInterval = setInterval(loadTrackingData, 30000);
        }
        
        // Manual check-in form
        const checkinForm = document.querySelector('#checkinForm');
        if (checkinForm) {
            initCheckinForm();
        }
    });
    
    function initMap() {
        // Initialize Leaflet map
        if (typeof L === 'undefined') {
            console.error('Leaflet library not loaded');
            return;
        }
        
        const mapConfig = window.AppConfig.map;
        
        map = L.map('trackingMap').setView(mapConfig.defaultCenter, mapConfig.defaultZoom);
        
        L.tileLayer(mapConfig.tileLayer, {
            attribution: mapConfig.attribution
        }).addTo(map);
    }
    
    function loadTrackingData() {
        const vehicleId = getVehicleIdFromUrl();
        if (!vehicleId) return;
        
        // Fetch tracking points from server
        fetch('/api/vehicles/tracking/get.php?vehicle_id=' + vehicleId + '&limit=20')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayTrackingPoints(data.points);
                }
            })
            .catch(error => {
                console.error('Error loading tracking data:', error);
            });
    }
    
    function displayTrackingPoints(points) {
        if (!map) return;
        
        // Clear existing markers
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];
        
        // Add markers for each point
        points.forEach((point, index) => {
            const marker = L.marker([point.latitude, point.longitude]).addTo(map);
            
            const popupContent = `
                <strong>Check-in ${index + 1}</strong><br>
                Time: ${point.timestamp}<br>
                Speed: ${point.speed || 'N/A'} km/h
            `;
            
            marker.bindPopup(popupContent);
            markers.push(marker);
        });
        
        // Draw path if multiple points
        if (points.length > 1) {
            const latlngs = points.map(p => [p.latitude, p.longitude]);
            L.polyline(latlngs, { color: '#26a69a', weight: 3 }).addTo(map);
        }
        
        // Center map on latest point
        if (points.length > 0) {
            const latest = points[0];
            map.setView([latest.latitude, latest.longitude], 15);
        }
    }
    
    function initCheckinForm() {
        const form = document.querySelector('#checkinForm');
        const getCurrentLocationBtn = document.querySelector('#getCurrentLocation');
        
        if (getCurrentLocationBtn) {
            getCurrentLocationBtn.addEventListener('click', function(e) {
                e.preventDefault();
                getCurrentLocation();
            });
        }
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitCheckin();
        });
    }
    
    function getCurrentLocation() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
            return;
        }
        
        const latInput = document.querySelector('#latitude');
        const lngInput = document.querySelector('#longitude');
        const btn = document.querySelector('#getCurrentLocation');
        
        btn.textContent = 'Getting location...';
        btn.disabled = true;
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                latInput.value = position.coords.latitude.toFixed(6);
                lngInput.value = position.coords.longitude.toFixed(6);
                btn.textContent = 'Get Current Location';
                btn.disabled = false;
                
                if (window.showToast) {
                    window.showToast('Location acquired successfully', 'success');
                }
            },
            function(error) {
                btn.textContent = 'Get Current Location';
                btn.disabled = false;
                alert('Error getting location: ' + error.message);
            }
        );
    }
    
    function submitCheckin() {
        const form = document.querySelector('#checkinForm');
        const formData = new FormData(form);
        
        fetch('/api/vehicles/tracking/checkin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (window.showToast) {
                    window.showToast('Check-in successful', 'success');
                }
                form.reset();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error submitting check-in:', error);
            alert('Error submitting check-in');
        });
    }
    
    function getVehicleIdFromUrl() {
        const params = new URLSearchParams(window.location.search);
        return params.get('vehicle_id');
    }
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (trackingInterval) {
            clearInterval(trackingInterval);
        }
    });
    
})();

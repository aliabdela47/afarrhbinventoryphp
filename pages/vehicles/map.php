<?php
/**
 * Vehicle Tracking Map
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// Require authentication
requireAuth();

$pageTitle = 'Vehicle Tracking - ' . APP_NAME;

try {
    // Get all vehicles with their latest locations
    $stmt = $pdo->query("
        SELECT v.*, vl.latitude, vl.longitude, vl.speed, vl.heading, vl.recorded_at,
               va.assignment_number, e.full_name as driver_name
        FROM VEHICLES v
        LEFT JOIN (
            SELECT vehicle_id, latitude, longitude, speed, heading, recorded_at
            FROM VEHICLE_LOCATIONS vl1
            WHERE recorded_at = (
                SELECT MAX(recorded_at) 
                FROM VEHICLE_LOCATIONS vl2 
                WHERE vl2.vehicle_id = vl1.vehicle_id
            )
        ) vl ON v.id = vl.vehicle_id
        LEFT JOIN VEHICLEASSIGNMENTS va ON v.id = va.vehicle_id AND va.status = 'active'
        LEFT JOIN EMPLIST e ON va.driver_id = e.id
        WHERE v.is_active = 1
        ORDER BY v.plate_number
    ");
    $vehicles = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Vehicle map error: " . $e->getMessage());
    $vehicles = [];
}

// Include header
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    #map {
        height: calc(100vh - 200px);
        min-height: 500px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .vehicle-card {
        border-left: 4px solid #6b46c1;
        transition: all 0.3s;
        cursor: pointer;
    }
    
    .vehicle-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    
    .vehicle-card.selected {
        border-left-color: #f5576c;
        background-color: #f8f9fa;
    }
    
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    
    .status-indicator.available {
        background-color: #11998e;
    }
    
    .status-indicator.assigned {
        background-color: #4facfe;
    }
    
    .status-indicator.maintenance {
        background-color: #f5576c;
    }
    
    .status-indicator.inactive {
        background-color: #6c757d;
    }
</style>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-geo-alt"></i> Vehicle Tracking</h2>
            <div>
                <button class="btn btn-info" onclick="refreshMap()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <a href="list.php" class="btn btn-secondary">
                    <i class="bi bi-list"></i> Vehicle List
                </a>
            </div>
        </div>
        
        <div class="row">
            <!-- Map -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-body p-0">
                        <div id="map"></div>
                    </div>
                </div>
            </div>
            
            <!-- Vehicle List -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Vehicles</h5>
                    </div>
                    <div class="card-body p-0" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                        <?php if (empty($vehicles)): ?>
                            <p class="text-muted text-center py-4">No vehicles found</p>
                        <?php else: ?>
                            <div id="vehicleList">
                                <?php foreach ($vehicles as $vehicle): ?>
                                <div class="vehicle-card card mb-2 mx-2 mt-2" 
                                     data-vehicle-id="<?php echo $vehicle['id']; ?>"
                                     data-lat="<?php echo $vehicle['latitude'] ?? ''; ?>"
                                     data-lng="<?php echo $vehicle['longitude'] ?? ''; ?>"
                                     onclick="selectVehicle(<?php echo $vehicle['id']; ?>)">
                                    <div class="card-body py-2 px-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    <span class="status-indicator <?php echo $vehicle['status']; ?>"></span>
                                                    <?php echo e($vehicle['plate_number']); ?>
                                                </h6>
                                                <small class="text-muted d-block">
                                                    <?php echo e($vehicle['make'] . ' ' . $vehicle['model']); ?>
                                                </small>
                                                <?php if ($vehicle['driver_name']): ?>
                                                <small class="text-info d-block">
                                                    <i class="bi bi-person"></i> <?php echo e($vehicle['driver_name']); ?>
                                                </small>
                                                <?php endif; ?>
                                                <?php if ($vehicle['latitude'] && $vehicle['longitude']): ?>
                                                <small class="text-success">
                                                    <i class="bi bi-geo-alt-fill"></i> Tracking
                                                </small>
                                                <?php if ($vehicle['speed']): ?>
                                                <small class="ms-2">
                                                    <i class="bi bi-speedometer2"></i> <?php echo number_format($vehicle['speed'], 1); ?> km/h
                                                </small>
                                                <?php endif; ?>
                                                <?php else: ?>
                                                <small class="text-muted">
                                                    <i class="bi bi-geo-alt"></i> No location data
                                                </small>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <span class="badge <?php 
                                                    echo ['available' => 'bg-success', 'assigned' => 'bg-info', 
                                                          'maintenance' => 'bg-warning', 'inactive' => 'bg-secondary'][$vehicle['status']]; 
                                                ?>">
                                                    <?php echo ucfirst($vehicle['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map;
    let markers = {};
    let selectedVehicleId = null;
    
    // Initialize map centered on Semera, Afar
    function initMap() {
        map = L.map('map').setView([11.7942, 40.9903], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Add vehicles to map
        addVehiclesToMap();
    }
    
    function addVehiclesToMap() {
        const vehicles = <?php echo json_encode($vehicles); ?>;
        
        vehicles.forEach(vehicle => {
            if (vehicle.latitude && vehicle.longitude) {
                const icon = L.divIcon({
                    className: 'vehicle-marker',
                    html: `<div style="background-color: ${getStatusColor(vehicle.status)}; 
                                width: 30px; height: 30px; border-radius: 50%; 
                                border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                                display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-truck" style="color: white; font-size: 14px;"></i>
                           </div>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });
                
                const marker = L.marker([vehicle.latitude, vehicle.longitude], { icon: icon })
                    .addTo(map);
                
                const popupContent = `
                    <div style="min-width: 200px;">
                        <h6><strong>${vehicle.plate_number}</strong></h6>
                        <p class="mb-1"><small>${vehicle.make} ${vehicle.model} (${vehicle.year})</small></p>
                        <p class="mb-1"><small><strong>Status:</strong> ${vehicle.status}</small></p>
                        ${vehicle.driver_name ? `<p class="mb-1"><small><strong>Driver:</strong> ${vehicle.driver_name}</small></p>` : ''}
                        ${vehicle.speed ? `<p class="mb-1"><small><strong>Speed:</strong> ${parseFloat(vehicle.speed).toFixed(1)} km/h</small></p>` : ''}
                        <a href="view.php?id=${vehicle.id}" class="btn btn-sm btn-primary mt-2">View Details</a>
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                markers[vehicle.id] = marker;
            }
        });
        
        // Fit bounds if there are markers
        if (Object.keys(markers).length > 0) {
            const group = L.featureGroup(Object.values(markers));
            map.fitBounds(group.getBounds().pad(0.1));
        }
    }
    
    function getStatusColor(status) {
        const colors = {
            'available': '#11998e',
            'assigned': '#4facfe',
            'maintenance': '#f5576c',
            'inactive': '#6c757d'
        };
        return colors[status] || '#6c757d';
    }
    
    function selectVehicle(vehicleId) {
        // Remove previous selection
        document.querySelectorAll('.vehicle-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Add selection to clicked card
        const card = document.querySelector(`[data-vehicle-id="${vehicleId}"]`);
        if (card) {
            card.classList.add('selected');
            selectedVehicleId = vehicleId;
            
            // Pan to marker and open popup
            if (markers[vehicleId]) {
                const marker = markers[vehicleId];
                map.setView(marker.getLatLng(), 15);
                marker.openPopup();
            }
        }
    }
    
    function refreshMap() {
        // In production, this would fetch updated vehicle locations via AJAX
        location.reload();
    }
    
    // Auto-refresh every 30 seconds (can be adjusted)
    setInterval(refreshMap, 30000);
    
    // Initialize map when page loads
    document.addEventListener('DOMContentLoaded', initMap);
</script>

<?php include '../../includes/footer.php'; ?>

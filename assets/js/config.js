/**
 * Configuration JavaScript
 * Global configuration for the application
 */

window.AppConfig = {
    // Base URL from PHP
    baseUrl: window.APP_URL || '',
    
    // API endpoints
    api: {
        changeLanguage: '/api/change-language.php',
        changeCalendar: '/api/change-calendar.php',
        vehicleTracking: '/api/vehicles/tracking/ingest.php'
    },
    
    // Default settings
    defaults: {
        language: 'en',
        calendar: 'gregorian',
        itemsPerPage: 20,
        dateFormat: 'DD/MM/YYYY',
        timeFormat: 'HH:mm:ss'
    },
    
    // Map settings
    map: {
        defaultCenter: [11.5742, 39.8302], // Addis Ababa
        defaultZoom: 13,
        tileLayer: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    },
    
    // File upload settings
    upload: {
        maxSize: 5242880, // 5MB
        allowedTypes: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']
    }
};

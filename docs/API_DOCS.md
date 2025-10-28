# Vehicle Tracking API Documentation

## Overview

The AfarRHB Inventory system includes a vehicle tracking API that allows mobile devices or GPS trackers to submit location data for vehicles. This enables real-time tracking and historical route analysis.

## Authentication

All API requests must include a valid API key in the request body or as a query parameter.

### API Key Structure

API keys are stored in the `VEHICLEAPIKEYS` table and consist of:
- **key_name**: A descriptive name for the key
- **api_key**: The actual key string (64 characters)
- **vehicle_id**: Optional - if set, key is restricted to specific vehicle
- **is_active**: Boolean flag to enable/disable key
- **expires_at**: Optional expiration timestamp

### Generating an API Key

API keys can be generated through the admin interface or directly in the database:

```sql
INSERT INTO VEHICLEAPIKEYS (key_name, api_key, user_id, is_active)
VALUES (
    'Mobile App Key',
    'YOUR_SECURE_RANDOM_64_CHAR_STRING_HERE',
    1,
    1
);
```

**Security Best Practice**: Generate cryptographically secure random strings for API keys:

```php
<?php
$apiKey = bin2hex(random_bytes(32)); // Generates 64-character hex string
?>
```

## Endpoints

### 1. Ingest Tracking Data (POST)

Submit vehicle location data.

**Endpoint**: `/api/vehicles/tracking/ingest.php`

**Method**: POST

**Content-Type**: application/json OR application/x-www-form-urlencoded

**Request Parameters**:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_key | string | Yes | Valid API key for authentication |
| vehicle_id | integer | Yes | ID of the vehicle being tracked |
| latitude | decimal | Yes | GPS latitude (-90 to 90) |
| longitude | decimal | Yes | GPS longitude (-180 to 180) |
| speed | decimal | No | Speed in km/h |
| heading | decimal | No | Compass heading in degrees (0-360) |
| altitude | decimal | No | Altitude in meters |
| accuracy | decimal | No | GPS accuracy in meters |
| timestamp | string | No | ISO 8601 timestamp (defaults to now) |

**JSON Example**:

```json
{
  "api_key": "TEST_API_KEY_12345_DO_NOT_USE_IN_PROD",
  "vehicle_id": 1,
  "latitude": 11.5751,
  "longitude": 39.8302,
  "speed": 45.5,
  "heading": 180.0,
  "altitude": 2400,
  "accuracy": 10.5,
  "timestamp": "2025-10-28T14:30:00Z"
}
```

**Form-Encoded Example**:

```
api_key=TEST_API_KEY_12345_DO_NOT_USE_IN_PROD&vehicle_id=1&latitude=11.5751&longitude=39.8302&speed=45.5
```

**Success Response**:

```json
{
  "success": true,
  "message": "Tracking data recorded successfully",
  "tracking_id": 123
}
```

**Error Response**:

```json
{
  "success": false,
  "error": "Invalid API key"
}
```

**HTTP Status Codes**:
- 200 OK - Success
- 400 Bad Request - Invalid parameters
- 401 Unauthorized - Invalid API key
- 500 Internal Server Error - Server error

### 2. Retrieve Tracking Data (GET)

Retrieve tracking history for a vehicle.

**Endpoint**: `/api/vehicles/tracking/get.php`

**Method**: GET

**Query Parameters**:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_key | string | Yes | Valid API key |
| vehicle_id | integer | Yes | ID of the vehicle |
| limit | integer | No | Number of records to return (default: 20, max: 100) |
| since | string | No | ISO 8601 timestamp to get records after |

**Example**:

```
GET /api/vehicles/tracking/get.php?api_key=YOUR_KEY&vehicle_id=1&limit=50
```

**Success Response**:

```json
{
  "success": true,
  "points": [
    {
      "id": 123,
      "latitude": 11.5751,
      "longitude": 39.8302,
      "speed": 45.5,
      "heading": 180.0,
      "altitude": 2400,
      "accuracy": 10.5,
      "timestamp": "2025-10-28T14:30:00Z",
      "source": "api"
    }
  ],
  "count": 1
}
```

## Usage Examples

### cURL (Command Line)

```bash
# Submit tracking data
curl -X POST https://inventory.afarrhb.gov.et/api/vehicles/tracking/ingest.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "YOUR_API_KEY",
    "vehicle_id": 1,
    "latitude": 11.5751,
    "longitude": 39.8302,
    "speed": 45.5
  }'

# Retrieve tracking data
curl "https://inventory.afarrhb.gov.et/api/vehicles/tracking/get.php?api_key=YOUR_API_KEY&vehicle_id=1&limit=20"
```

### JavaScript (Fetch API)

```javascript
// Submit tracking data
async function submitTracking(vehicleId, lat, lng) {
  const response = await fetch('/api/vehicles/tracking/ingest.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      api_key: 'YOUR_API_KEY',
      vehicle_id: vehicleId,
      latitude: lat,
      longitude: lng,
      speed: 45.5,
      timestamp: new Date().toISOString()
    })
  });
  
  const data = await response.json();
  return data;
}

// Get current position and submit
if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition((position) => {
    submitTracking(
      1,
      position.coords.latitude,
      position.coords.longitude
    );
  });
}
```

### PHP

```php
<?php
$data = [
    'api_key' => 'YOUR_API_KEY',
    'vehicle_id' => 1,
    'latitude' => 11.5751,
    'longitude' => 39.8302,
    'speed' => 45.5,
    'timestamp' => date('c')
];

$ch = curl_init('https://inventory.afarrhb.gov.et/api/vehicles/tracking/ingest.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
?>
```

## Mobile Integration

### Android (Java/Kotlin)

Use Android Location Services to get GPS coordinates and submit them periodically:

```kotlin
// Kotlin example
val apiKey = "YOUR_API_KEY"
val vehicleId = 1

locationCallback = object : LocationCallback() {
    override fun onLocationResult(locationResult: LocationResult) {
        val location = locationResult.lastLocation
        
        val json = JSONObject().apply {
            put("api_key", apiKey)
            put("vehicle_id", vehicleId)
            put("latitude", location.latitude)
            put("longitude", location.longitude)
            put("speed", location.speed * 3.6) // Convert m/s to km/h
            put("heading", location.bearing)
            put("altitude", location.altitude)
            put("accuracy", location.accuracy)
            put("timestamp", Instant.now().toString())
        }
        
        // Submit to API using Retrofit, Volley, or OkHttp
    }
}
```

### iOS (Swift)

```swift
// Swift example
func submitLocation(location: CLLocation) {
    let url = URL(string: "https://inventory.afarrhb.gov.et/api/vehicles/tracking/ingest.php")!
    var request = URLRequest(url: url)
    request.httpMethod = "POST"
    request.setValue("application/json", forHTTPHeaderField: "Content-Type")
    
    let json: [String: Any] = [
        "api_key": "YOUR_API_KEY",
        "vehicle_id": 1,
        "latitude": location.coordinate.latitude,
        "longitude": location.coordinate.longitude,
        "speed": location.speed * 3.6, // Convert m/s to km/h
        "heading": location.course,
        "altitude": location.altitude,
        "accuracy": location.horizontalAccuracy,
        "timestamp": ISO8601DateFormatter().string(from: Date())
    ]
    
    request.httpBody = try? JSONSerialization.data(withJSONObject: json)
    
    URLSession.shared.dataTask(with: request) { data, response, error in
        // Handle response
    }.resume()
}
```

## Security Considerations

1. **HTTPS Only**: In production, ensure all API endpoints use HTTPS to encrypt data in transit
2. **API Key Rotation**: Regularly rotate API keys
3. **Rate Limiting**: Implement rate limiting to prevent abuse
4. **IP Whitelisting**: Consider restricting API access to specific IP ranges
5. **Key Expiration**: Set expiration dates for API keys
6. **Audit Logging**: All API requests are logged for security auditing

## Rate Limits

Default rate limits (can be configured):
- 100 requests per minute per API key
- 1000 requests per hour per API key

Exceeding rate limits returns HTTP 429 (Too Many Requests).

## Error Codes

| Code | Message | Description |
|------|---------|-------------|
| 400 | Missing required parameter | Required field not provided |
| 401 | Invalid API key | API key not found or inactive |
| 401 | API key expired | API key has passed expiration date |
| 403 | Vehicle access denied | API key not authorized for this vehicle |
| 404 | Vehicle not found | Vehicle ID does not exist |
| 422 | Invalid coordinates | Latitude/longitude out of valid range |
| 429 | Rate limit exceeded | Too many requests in time window |
| 500 | Internal server error | Server-side error occurred |

## Testing

Use the test API key for development:

```
TEST_API_KEY_12345_DO_NOT_USE_IN_PROD
```

**Warning**: This key is only for testing and will be disabled in production.

## Support

For API support, contact:
- Email: it@afarrhb.gov.et
- Phone: +251-XXX-XXXXXX

## Changelog

### Version 1.0 (2025-10-28)
- Initial API release
- POST ingest endpoint
- GET retrieval endpoint
- API key authentication

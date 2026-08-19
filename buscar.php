<?php
// 1. Configuramos el archivo para que responda en formato JSON puro
header("Content-Type: application/json");

// 2. Recibimos los datos que nos enviará la app Android
// Usamos (float) para forzar que sean números decimales y evitar errores
$lat_usuario = isset($_GET['lat']) ? (float)$_GET['lat'] : 0.0;
$lon_usuario = isset($_GET['lon']) ? (float)$_GET['lon'] : 0.0;

// Si no recibimos coordenadas válidas, detenemos la ejecución y avisamos
if ($lat_usuario === 0.0 || $lon_usuario === 0.0) {
    echo json_encode(["error" => "Coordenadas no válidas"]);
    exit;
}

// 3. Función rigurosa: Fórmula de Haversine para calcular distancias reales en la Tierra
function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
    $radioTierra = 6371; // Radio de la Tierra en kilómetros
    
    // Convertimos los grados a radianes
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    // Aplicamos la fórmula matemática
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
         
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $radioTierra * $c; // Devuelve la distancia en kilómetros
}

// 4. Simulamos una base de datos (En el futuro, esto vendrá de la API del Gobierno)
$gasolineras = [
    ["nombre" => "Gasolinera Centro", "lat" => 40.4168, "lon" => -3.7038, "precio_diesel" => 1.55],
    ["nombre" => "Gasolinera Periferia", "lat" => 40.4500, "lon" => -3.7000, "precio_diesel" => 1.49],
    ["nombre" => "Gasolinera Lejana", "lat" => 40.3800, "lon" => -3.7100, "precio_diesel" => 1.60]
];

$resultados = [];

// 5. Calculamos la distancia para cada gasolinera
foreach($gasolineras as $gas) {
    $distancia = calcularDistancia($lat_usuario, $lon_usuario, $gas['lat'], $gas['lon']);
    
    // Guardamos solo si está a menos de 15 kilómetros (por ejemplo)
    if ($distancia < 15) {
        $resultados[] = [
            "nombre" => $gas['nombre'],
            "precio" => $gas['precio_diesel'],
            "distancia_km" => round($distancia, 2)
        ];
    }
}

// 6. Ordenamos los resultados de menor a mayor precio
usort($resultados, function($a, $b) {
    return $a['precio'] <=> $b['precio'];
});

// 7. Empaquetamos la respuesta en JSON y se la devolvemos a Android
// Devolvemos todo el array de resultados para que el móvil decida cómo mostrarlos
echo json_encode([
    "exito" => true, 
    "coordenadas_recibidas" => ["lat" => $lat_usuario, "lon" => $lon_usuario],
    "gasolineras_encontradas" => $resultados
]);
?>
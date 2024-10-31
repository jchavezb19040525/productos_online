<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

$server = 'ep-icy-lab-a5ainjd0.us-east-2.aws.neon.tech';
$username = 'u4_crud_21040520_owner';
$puerto = '5432';
$password = '96ArxyfFkwBq';
$database = 'u4_crud_21040520';
$endpoint = 'ep-icy-lab-a5ainjd0';

try {
    $dsn = "pgsql:host=$server;port=$puerto;dbname=$database;sslmode=require;options=endpoint=$endpoint";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión: ' . $e->getMessage()
    ]);
    exit;
}

// Función para crear un estudiante
function crearEstudiante($conn, $nombre, $carrera) {
    try {
        $sql = "INSERT INTO registro (nombre, carrera) VALUES (:nombre, :carrera)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute(['nombre' => $nombre, 'carrera' => $carrera])) {
            return [
                "success" => true, 
                "message" => "Estudiante creado exitosamente"
            ];
        } else {
            return [
                "success" => false, 
                "message" => "Error al crear el estudiante"
            ];
        }
    } catch (PDOException $e) {
        return [
            "success" => false, 
            "message" => "Error: " . $e->getMessage()
        ];
    }
}

// Función para buscar un estudiante
function buscarEstudiante($conn, $no_control) {
    try {
        $sql = "SELECT * FROM registro WHERE no_control = :no_control";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['no_control' => $no_control]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            return [
                "success" => true,
                "data" => $resultado
            ];
        } else {
            return [
                "success" => false,
                "message" => "No se encontró ningún registro"
            ];
        }
    } catch (PDOException $e) {
        return [
            "success" => false,
            "message" => "Error en la búsqueda: " . $e->getMessage()
        ];
    }
}

// Función para actualizar un estudiante
function actualizarEstudiante($conn, $no_control, $nombre, $carrera) {
    try {
        // Primero verificamos si el estudiante existe
        $checkSql = "SELECT COUNT(*) FROM registro WHERE no_control = :no_control";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute(['no_control' => $no_control]);
        $exists = $checkStmt->fetchColumn();

        if ($exists == 0) {
            return [
                "success" => false,
                "message" => "No se encontró el estudiante con el número de control proporcionado"
            ];
        }

        // Si existe, procedemos a actualizar
        $sql = "UPDATE registro SET nombre = :nombre, carrera = :carrera WHERE no_control = :no_control";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute(['nombre' => $nombre, 'carrera' => $carrera, 'no_control' => $no_control])) {
            return [
                "success" => true,
                "message" => "Registro actualizado exitosamente"
            ];
        } else {
            return [
                "success" => false,
                "message" => "Error al actualizar el registro"
            ];
        }
    } catch (PDOException $e) {
        return [
            "success" => false,
            "message" => "Error en la actualización: " . $e->getMessage()
        ];
    }
}

// Función para eliminar un estudiante
function eliminarEstudiante($conn, $no_control) {
    try {
        $sql = "DELETE FROM registro WHERE no_control = :no_control";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute(['no_control' => $no_control])) {
            if ($stmt->rowCount() > 0) {
                return [
                    "success" => true,
                    "message" => "Registro eliminado exitosamente"
                ];
            } else {
                return [
                    "success" => false,
                    "message" => "No se encontró el registro a eliminar"
                ];
            }
        } else {
            return [
                "success" => false,
                "message" => "Error al eliminar el registro"
            ];
        }
    } catch (PDOException $e) {
        return [
            "success" => false,
            "message" => "Error en la eliminación: " . $e->getMessage()
        ];
    }
}

// Función para obtener el máximo no_control
function obtenerMaxNoControl($conn) {
    try {
        $stmt = $conn->query("SELECT MAX(no_control) AS max_no_control FROM registro");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['max_no_control'])) {
            return [
                "success" => true,
                "max_no_control" => $result['max_no_control']
            ];
        } else {
            return [
                "success" => true,
                "max_no_control" => 0
            ];
        }
    } catch (PDOException $e) {
        return [
            "success" => false,
            "message" => $e->getMessage()
        ];
    }
}

// Router principal
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST["action"]) ? $_POST["action"] : "";
    $response = [];
    
    switch ($action) {
        case "crear":
            $response = crearEstudiante($conn, $_POST["nombre"], $_POST["carrera"]);
            break;
            
        case "buscar":
            $response = buscarEstudiante($conn, $_POST["no_control"]);
            break;
            
        case "actualizar":
            $response = actualizarEstudiante($conn, $_POST["no_control"], $_POST["nombre"], $_POST["carrera"]);
            break;
            
        case "eliminar":
            $response = eliminarEstudiante($conn, $_POST["no_control"]);
            break;
            
        case "maxNoControl":
            $response = obtenerMaxNoControl($conn);
            break;
            
        default:
            $response = [
                "success" => false,
                "message" => "Acción no válida"
            ];
            break;
    }
    
    echo json_encode($response);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Método no permitido"
    ]);
}
?>
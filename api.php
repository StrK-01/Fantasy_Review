<?php
// fantasy_review/api.php
session_start(); // Start the session for user authentication
include('database/db.php');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$data = [];

// Handle input data based on method
if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
} elseif ($method === 'GET') {
    $data = $_GET;
}

if (($method === 'POST' || $method === 'PUT' || $method === 'DELETE') && $data === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input.']);
    $conn->close();
    exit;
}

// --- AUTHENTICATION FUNCTIONS ---

function handleRegister($conn, $data) {
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        return ['status' => 'error', 'message' => 'Username and password are required.'];
    }

    // Hash the password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the users table
    $query = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    
    // Check for duplicate username (a better check might be needed if unique constraint fails silently)
    if (!$stmt) {
        http_response_code(500);
        return ['status' => 'error', 'message' => 'Database prepare error: ' . $conn->error];
    }

    $stmt->bind_param("ss", $username, $password_hash);
    
    if ($stmt->execute()) {
        $stmt->close();
        return ['status' => 'success', 'message' => 'Account created successfully!'];
    } else {
        $stmt->close();
        // Check for duplicate key error (MySQL error code 1062)
        if ($conn->errno === 1062) {
            http_response_code(409); // Conflict
            return ['status' => 'error', 'message' => 'Username already exists. Please choose a different one.'];
        }
        http_response_code(500);
        return ['status' => 'error', 'message' => 'Account creation failed: ' . $conn->error];
    }
}

function handleLogin($conn, $data) {
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        return ['status' => 'error', 'message' => 'Username and password are required.'];
    }

    $query = "SELECT id, password_hash FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        http_response_code(500);
        return ['status' => 'error', 'message' => 'Database prepare error: ' . $conn->error];
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password_hash'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $stmt->close();
            return ['status' => 'success', 'message' => 'Login successful!', 'username' => $username];
        }
    }

    $stmt->close();
    http_response_code(401); // Unauthorized
    return ['status' => 'error', 'message' => 'Invalid username or password.'];
}


// --- MAIN API ROUTER ---

// Handle Authentication POST requests
if ($method === 'POST' && isset($data['action'])) {
    if ($data['action'] === 'register') {
        $response = handleRegister($conn, $data);
        echo json_encode($response);
        $conn->close();
        exit;
    } elseif ($data['action'] === 'login') {
        $response = handleLogin($conn, $data);
        echo json_encode($response);
        $conn->close();
        exit;
    }
}


switch ($method) {
    case 'GET': // READ REVIEWS
        // New: Handle single review request
        if (isset($data['action']) && $data['action'] === 'get_single_review' && isset($data['id'])) {
            $id = (int)($data['id'] ?? 0);
            $query = "SELECT id, title, review_text, rating, poster FROM reviews WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $review = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'review' => $review]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Review not found.']);
            }
            $stmt->close();
            break; // Exit the switch case after handling single review request
        }
        
        // Original logic for fetching all/searched reviews
        $search = $data['search'] ?? '';
        
        $query = "SELECT id, title, review_text, rating, poster FROM reviews";
        $params = [];
        $types = "";

        if (!empty($search)) {
            $query .= " WHERE title LIKE ?";
            $params[] = "%" . $search . "%";
            $types .= "s";
        }
        
        $query .= " ORDER BY created_at DESC";

        $stmt = $conn->prepare($query);

        if (!empty($params)) {
            // Dynamically bind parameters
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reviews = [];
        while($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }

        echo json_encode(['status' => 'success', 'reviews' => $reviews]);
        $stmt->close();
        break;

    case 'POST': // CREATE REVIEW
        $title = $data['title'] ?? '';
        $review_text = $data['review_text'] ?? '';
        $rating = (int)($data['rating'] ?? 0);
        $poster = $data['poster'] ?? ''; // Optional

        if (empty($title) || empty($review_text) || $rating < 1 || $rating > 5) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Title, review text, and a valid rating (1-5) are required.']);
            break;
        }

        $query = "INSERT INTO reviews (title, review_text, rating, poster) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssis", $title, $review_text, $rating, $poster);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Review added successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error during insert: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'PUT': // UPDATE REVIEW
        $id = (int)($data['id'] ?? 0);
        $title = $data['title'] ?? '';
        $review_text = $data['review_text'] ?? '';
        $rating = (int)($data['rating'] ?? 0);
        // Note: poster update is not included in the form, but could be added here if needed.

        if (empty($id) || empty($title) || empty($review_text) || $rating < 1 || $rating > 5) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Review ID, title, review text, and a valid rating (1-5) are required for update.']);
            break;
        }

        $query = "UPDATE reviews SET title = ?, review_text = ?, rating = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $title, $review_text, $rating, $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Review updated successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error during update: ' . $stmt->error]);
        }
        $stmt->close();
        break;
        
    case 'DELETE': // DELETE REVIEW
        $id = (int)($data['id'] ?? 0);
        
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Review ID missing for delete.']);
            break;
        }

        $query = "DELETE FROM reviews WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Review deleted successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error during delete: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['status' => 'error', 'message' => 'Method not supported.']);
        break;
}

$conn->close();
?>
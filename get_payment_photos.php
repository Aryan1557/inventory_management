<?php
session_start();
include "db_connection.php";
include 'session_check.php';

header('Content-Type: application/json');

// ============================================
// DELETE PHOTO
// ============================================
if (isset($_GET['delete_photo'])) {
    $photo_id = (int) $_GET['delete_photo'];
    
    // Get photo path first
    $get_photo = mysqli_query($conn, "SELECT photo_path FROM payment_photos WHERE id = '$photo_id'");
    if ($photo = mysqli_fetch_assoc($get_photo)) {
        if (!empty($photo['photo_path']) && file_exists($photo['photo_path'])) {
            unlink($photo['photo_path']);
        }
    }
    
    $delete = mysqli_query($conn, "DELETE FROM payment_photos WHERE id = '$photo_id'");
    
    if ($delete) {
        echo json_encode(['success' => true, 'message' => 'Photo deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete photo']);
    }
    exit;
}

// ============================================
// GET PHOTOS FOR CLIENT
// ============================================
if (isset($_GET['client_id'])) {
    $client_id = (int) $_GET['client_id'];
    
    $query = "SELECT id, payment_date, photo_path, amount FROM payment_photos WHERE client_id = '$client_id' ORDER BY payment_date DESC, id DESC";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
        exit;
    }
    
    $groups = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Check if file exists, if not, still show the record but mark it
        $file_exists = file_exists($row['photo_path']);
        
        $date = $row['payment_date'];
        if (!isset($groups[$date])) {
            $groups[$date] = [];
        }
        
        // Only add if file exists, otherwise skip
        if ($file_exists) {
            $groups[$date][] = $row;
        }
    }
    
    // Format groups for display
    $formatted_groups = [];
    foreach ($groups as $date => $date_photos) {
        $formatted_groups[] = [
            'payment_date' => $date,
            'photos' => $date_photos
        ];
    }
    
    echo json_encode([
        'success' => true,
        'groups' => $formatted_groups
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
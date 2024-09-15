<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

//check if rescuer is inside the citizen's 50m circle
if (!isset($_SESSION['isInsideCircleCitizen']) || !$_SESSION['isInsideCircleCitizen']) {
    echo "You must be within the 50m radius to complete this task.";
    exit();
}
include '../db_connect.php';

if (isset($_GET['task_id'])) {
    $task_id = intval($_GET['task_id']);
    $rescuer_id = $_SESSION['user_id'];

    // Fetch the task type to determine how to handle the inventory
    $sql_task = "SELECT task_type, request_id, offer_id FROM tasks WHERE task_id = ?";
    $stmt_task = $conn->prepare($sql_task);
    $stmt_task->bind_param('i', $task_id);
    $stmt_task->execute();
    $task_result = $stmt_task->get_result();
    $task = $task_result->fetch_assoc();
    
    if ($task) {
        if ($task['task_type'] == 'request') {
            // Update inventory for request (reduce item quantity)
            $sql_request = "SELECT r.item_id, r.quantity FROM requests r WHERE r.id = ?";
            $stmt_request = $conn->prepare($sql_request);
            $stmt_request->bind_param('i', $task['request_id']);
            $stmt_request->execute();
            $request_result = $stmt_request->get_result();
            $request = $request_result->fetch_assoc();

            if ($request) {
                $item_id = $request['item_id'];
                $quantity_to_deduct = $request['quantity'];

                // Deduct the quantity from the rescuer's inventory
                $sql_update_inventory = "UPDATE inventory SET quantity = quantity - ? WHERE rescuer_id = ? AND item_id = ?";
                $stmt_update_inventory = $conn->prepare($sql_update_inventory);
                $stmt_update_inventory->bind_param('iii', $quantity_to_deduct, $rescuer_id, $item_id);
                $stmt_update_inventory->execute();
            }
        } elseif ($task['task_type'] == 'offer') {
            // Update inventory for offer (increase item quantity)
            $sql_offer = "SELECT o.item_ids FROM offers o WHERE o.id = ?";
            $stmt_offer = $conn->prepare($sql_offer);
            $stmt_offer->bind_param('i', $task['offer_id']);
            $stmt_offer->execute();
            $offer_result = $stmt_offer->get_result();
            $offer = $offer_result->fetch_assoc();

            if ($offer) {
                $item_quantities = explode(',', $offer['item_ids']); 

                foreach ($item_quantities as $item_quantity) {
                    list($item_id, $quantity) = explode(':', $item_quantity);

                    // Update inventory with the offered quantity
                    $sql_check_inventory = "SELECT quantity FROM inventory WHERE rescuer_id = ? AND item_id = ?";
                    $stmt_check_inventory = $conn->prepare($sql_check_inventory);
                    $stmt_check_inventory->bind_param('ii', $rescuer_id, $item_id);
                    $stmt_check_inventory->execute();
                    $inventory_result = $stmt_check_inventory->get_result();

                    if ($inventory_result->num_rows > 0) {
                        // Update existing inventory
                        $sql_update_inventory = "UPDATE inventory SET quantity = quantity + ? WHERE rescuer_id = ? AND item_id = ?";
                        $stmt_update_inventory = $conn->prepare($sql_update_inventory);
                        $stmt_update_inventory->bind_param('iii', $quantity, $rescuer_id, $item_id);
                        $stmt_update_inventory->execute();
                    } else {
                        // Insert new item into inventory
                        $sql_insert_inventory = "INSERT INTO inventory (rescuer_id, item_id, quantity) VALUES (?, ?, ?)";
                        $stmt_insert_inventory = $conn->prepare($sql_insert_inventory);
                        $stmt_insert_inventory->bind_param('iii', $rescuer_id, $item_id, $quantity);
                        $stmt_insert_inventory->execute();
                    }
                }
            }
        }

        // Update the task status to 'completed'
        $sql_update = "UPDATE tasks SET status = 'completed', completed_at = NOW() WHERE task_id = ? AND rescuer_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('ii', $task_id, $rescuer_id);
        $stmt_update->execute();

        // Update the status of the corresponding request or offer
        if ($task['task_type'] == 'request') {
            $sql_update_request = "UPDATE requests SET status = 'completed' WHERE id = ?";
            $stmt_update_request = $conn->prepare($sql_update_request);
            $stmt_update_request->bind_param('i', $task['request_id']);
            $stmt_update_request->execute();
        } elseif ($task['task_type'] == 'offer') {
            $sql_update_offer = "UPDATE offers SET status = 'completed' WHERE id = ?";
            $stmt_update_offer = $conn->prepare($sql_update_offer);
            $stmt_update_offer->bind_param('i', $task['offer_id']);
            $stmt_update_offer->execute();
        }

        header("Location: view_completed_tasks.php");
        exit();
    }
}

$conn->close();
?>

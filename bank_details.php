<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get bank details
$bank_result = $conn->query("
    SELECT bank_name, account_holder_name, account_number, branch 
    FROM bank_details 
    WHERE user_id = $user_id
");
$bank_details = $bank_result->num_rows > 0 ? $bank_result->fetch_assoc() : null;

// Handle bank details update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_bank'])) {
    $bank_name = trim($_POST['bank_name']);
    $account_holder_name = trim($_POST['account_holder_name']);
    $account_number = trim($_POST['account_number']);
    $branch = trim($_POST['branch']);
    
    // Validation
    $errors = [];
    
    if (empty($bank_name)) {
        $errors[] = "Bank name is required";
    }
    
    if (empty($account_holder_name)) {
        $errors[] = "Account holder name is required";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $account_holder_name)) {
        $errors[] = "Account holder name can only contain letters and spaces";
    }
    
    if (empty($account_number)) {
        $errors[] = "Account number is required";
    } elseif (!preg_match('/^[0-9]+$/', $account_number)) {
        $errors[] = "Account number can only contain numbers";
    }
    
    if (empty($branch)) {
        $errors[] = "Branch is required";
    }
    
    if (empty($errors)) {
        if ($bank_details) {
            // Update existing record
            $stmt = $conn->prepare("
                UPDATE bank_details 
                SET bank_name = ?, account_holder_name = ?, account_number = ?, branch = ? 
                WHERE user_id = ?
            ");
            $stmt->bind_param("ssssi", $bank_name, $account_holder_name, $account_number, $branch, $user_id);
        } else {
            // Insert new record
            $stmt = $conn->prepare("
                INSERT INTO bank_details (user_id, bank_name, account_holder_name, account_number, branch) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("issss", $user_id, $bank_name, $account_holder_name, $account_number, $branch);
        }
        
        if ($stmt->execute()) {
            $success = "Bank details updated successfully!";
            // Refresh bank details
            header("Location: bank_details.php");
            exit();
        } else {
            $error = "Error updating bank details: " . $conn->error;
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Details - Guess the Number</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Bank Details</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="bank_name" class="form-label">Bank Name *</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                       value="<?php echo $bank_details ? htmlspecialchars($bank_details['bank_name']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="account_holder_name" class="form-label">Account Holder Name *</label>
                                <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" 
                                       value="<?php echo $bank_details ? htmlspecialchars($bank_details['account_holder_name']) : ''; ?>" 
                                       pattern="[a-zA-Z\s]+" title="Only letters and spaces" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="account_number" class="form-label">Account Number *</label>
                                <input type="text" class="form-control" id="account_number" name="account_number" 
                                       value="<?php echo $bank_details ? htmlspecialchars($bank_details['account_number']) : ''; ?>" 
                                       pattern="[0-9]+" title="Only numbers" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="branch" class="form-label">Branch *</label>
                                <input type="text" class="form-control" id="branch" name="branch" 
                                       value="<?php echo $bank_details ? htmlspecialchars($bank_details['branch']) : ''; ?>" required>
                            </div>
                            
                            <button type="submit" name="update_bank" class="btn btn-primary w-100">Save Bank Details</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
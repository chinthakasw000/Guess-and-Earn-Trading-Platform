<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user profile
$profile_result = $conn->query("
    SELECT u.username, u.email, u.created_at, u.last_login,
           p.full_name, p.avatar_url, p.country, p.date_of_birth
    FROM users u 
    LEFT JOIN user_profiles p ON u.id = p.user_id 
    WHERE u.id = $user_id
");
$profile = $profile_result->fetch_assoc();

// Set default avatar if none
$profile['avatar_url'] = !empty($profile['avatar_url']) ? $profile['avatar_url'] : 'https://avatar.iran.liara.run/public';

// Get game stats
$stats_result = $conn->query("
    SELECT total_games, easy_wins, normal_wins, advance_wins, total_earnings 
    FROM game_stats 
    WHERE user_id = $user_id
");
$stats = $stats_result->fetch_assoc();

// Fetch countries for dropdown
$country_result = $conn->query("SELECT name FROM countries ORDER BY name ASC");
$countries = [];
while ($row = $country_result->fetch_assoc()) {
    $countries[] = $row['name'];
}

// Handle profile update
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        $country = trim($_POST['country']);
        $date_of_birth = $_POST['date_of_birth'];

        // Server-side validation
        if (empty($full_name)) {
            $error = "Full name is required.";
        } elseif (strlen($full_name) < 2) {
            $error = "Full name must be at least 2 characters long.";
        } elseif (empty($country)) {
            $error = "Country is required.";
        } elseif (empty($date_of_birth)) {
            $error = "Date of birth is required.";
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_of_birth)) {
            $error = "Date of birth format is invalid.";
        } else {
            // Check age >= 13
            $dob_timestamp = strtotime($date_of_birth);
            $age = (int) ((time() - $dob_timestamp) / (365.25*24*60*60));
            if ($age < 13) {
                $error = "You must be at least 13 years old.";
            }
        }

        // If no validation errors, update profile
        if (empty($error)) {
            $stmt = $conn->prepare("
                UPDATE user_profiles 
                SET full_name = ?, country = ?, date_of_birth = ? 
                WHERE user_id = ?
            ");
            $stmt->bind_param("sssi", $full_name, $country, $date_of_birth, $user_id);

            if ($stmt->execute()) {
                $success = "Profile updated successfully!";
                // Refresh profile data
                header("Location: profile.php");
                exit();
            } else {
                $error = "Error updating profile: " . $conn->error;
            }
        }
    }

    // Handle avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['avatar']['type'];

        if (in_array($file_type, $allowed_types)) {
            $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
            $upload_path = 'uploads/' . $filename;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                // Delete old avatar if not default
                if ($profile['avatar_url'] && $profile['avatar_url'] !== 'https://avatar.iran.liara.run/public') {
                    @unlink($profile['avatar_url']);
                }
                $conn->query("UPDATE user_profiles SET avatar_url = '$upload_path' WHERE user_id = $user_id");
                $success = "Avatar updated successfully!";
                header("Location: profile.php");
                exit();
            } else {
                $error = "Failed to upload avatar.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG and GIF are allowed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Guess the Number</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-body text-center">
                        <img src="<?php echo $profile['avatar_url']; ?>" 
                             alt="Avatar" class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">

                        <h5 class="my-3"><?php echo htmlspecialchars($profile['username']); ?></h5>
                        <p class="text-muted mb-1">Member since: <?php echo date('M Y', strtotime($profile['created_at'])); ?></p>
                        <p class="text-muted mb-4">Last login: <?php echo $profile['last_login'] ? date('M j, Y g:i A', strtotime($profile['last_login'])) : 'Never'; ?></p>

                        <!-- Avatar Upload Form -->
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="avatar" class="form-label">Update Avatar</label>
                                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif">
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-sm">Upload Avatar</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header py-3">
                        <h5 class="m-0 font-weight-bold text-primary">Game Statistics</h5>
                    </div>
                    <div class="card-body">
                        <p>Total Games: <span class="float-end"><?php echo $stats['total_games']; ?></span></p>
                        <p>Easy Wins: <span class="float-end"><?php echo $stats['easy_wins']; ?></span></p>
                        <p>Normal Wins: <span class="float-end"><?php echo $stats['normal_wins']; ?></span></p>
                        <p>Advance Wins: <span class="float-end"><?php echo $stats['advance_wins']; ?></span></p>
                        <hr>
                        <p class="fw-bold">Total Earnings: <span class="float-end text-success">$<?php echo number_format($stats['total_earnings'], 3); ?></span></p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h5 class="m-0 font-weight-bold text-primary">Profile Details</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" id="profileForm">
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <p class="mb-0">Username</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($profile['username']); ?></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <p class="mb-0">Email</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($profile['email']); ?></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="full_name" class="mb-0">Full Name</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>" required minlength="2">
                                </div>
                            </div>

                            <!-- Country Dropdown -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="country" class="mb-0">Country</label>
                                </div>
                                <div class="col-sm-9">
                                    <select class="form-select" id="country" name="country" required>
                                        <option value="">-- Select Country --</option>
                                        <?php foreach ($countries as $c): ?>
                                            <option value="<?php echo htmlspecialchars($c); ?>" 
                                                <?php echo (isset($profile['country']) && $profile['country'] === $c) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($c); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="date_of_birth" class="mb-0">Date of Birth</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                           value="<?php echo $profile['date_of_birth'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Client-side validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const fullName = document.getElementById('full_name').value.trim();
            const country = document.getElementById('country').value;
            const dob = document.getElementById('date_of_birth').value;

            let errors = [];

            if (fullName.length < 2) errors.push("Full name must be at least 2 characters.");
            if (!country) errors.push("Country is required.");
            if (!dob) errors.push("Date of birth is required.");

            if (dob) {
                const birthDate = new Date(dob);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                if (age < 13) errors.push("You must be at least 13 years old.");
            }

            if (errors.length > 0) {
                alert(errors.join("\n"));
                e.preventDefault();
            }
        });
    </script>
</body>
</html>

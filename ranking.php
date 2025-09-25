<?php
session_start();
include 'db_connect.php';

// Get top players by earnings
$ranking_result = $conn->query("
    SELECT u.username, p.full_name, gs.total_earnings, gs.total_games,
           gs.easy_wins, gs.normal_wins, gs.advance_wins
    FROM game_stats gs
    JOIN users u ON gs.user_id = u.id
    LEFT JOIN user_profiles p ON u.id = p.user_id
    ORDER BY gs.total_earnings DESC
    LIMIT 50
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Guess the Number</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Top Players Leaderboard</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Rank</th>
                                        <th>Player</th>
                                        <th>Total Earnings</th>
                                        <th>Total Games</th>
                                        <th>Easy Wins</th>
                                        <th>Normal Wins</th>
                                        <th>Advance Wins</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $rank = 1;
                                    while ($player = $ranking_result->fetch_assoc()):
                                        $username = htmlspecialchars($player['username']);
                                        $full_name = !empty($player['full_name']) ? htmlspecialchars($player['full_name']) : $username;
                                    ?>
                                    <tr>
                                        <td><?php echo $rank; ?></td>
                                        <td><?php echo $full_name; ?></td>
                                        <td class="text-success fw-bold">$<?php echo number_format($player['total_earnings'], 3); ?></td>
                                        <td><?php echo $player['total_games']; ?></td>
                                        <td><?php echo $player['easy_wins']; ?></td>
                                        <td><?php echo $player['normal_wins']; ?></td>
                                        <td><?php echo $player['advance_wins']; ?></td>
                                    </tr>
                                    <?php 
                                    $rank++;
                                    endwhile; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
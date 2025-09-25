<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Update game stats when a win occurs
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['game_result'])) {
  $mode = $_POST['mode'];
  $amount = floatval($_POST['amount']);
  $won = $_POST['won'] == 'true';

  // Update game stats
  $field = $mode . '_wins';
  if ($won) {
    $conn->query("
            UPDATE game_stats 
            SET total_games = total_games + 1, 
                $field = $field + 1, 
                total_earnings = total_earnings + $amount 
            WHERE user_id = $user_id
        ");
  } else {
    $conn->query("
            UPDATE game_stats 
            SET total_games = total_games + 1 
            WHERE user_id = $user_id
        ");
  }

  // Record game session
  if ($won) {
    $conn->query("
            INSERT INTO game_sessions (user_id, game_mode, amount_won) 
            VALUES ($user_id, '$mode', $amount)
        ");
  } else {
    $conn->query("
            INSERT INTO game_sessions (user_id, game_mode, amount_won) 
            VALUES ($user_id, '$mode', 0)
        ");
  }

  echo json_encode(['status' => 'success']);
  exit();
}

// Get current earnings
$result = $conn->query("SELECT total_earnings FROM game_stats WHERE user_id = $user_id");
$earnings = $result->fetch_assoc()['total_earnings'];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guess</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="">
  <?php include 'navbar.php'; ?>

  <div class="d-flex justify-content-center align-items-center vh-100 bg-light">

    <div class="card shadow-lg p-4 text-center" style="max-width: 600px; width: 100%;">
      <h3 class="mb-4">🎯 Guess the Number!</h3>
      <p class="text-muted">Welcome, <?php echo $_SESSION['username']; ?>!</p>

      <!-- Mode Switch Buttons -->
      <div class="mb-3">
        <button id="easyModeBtn" class="btn btn-outline-primary me-2">Easy</button>
        <button id="normalModeBtn" class="btn btn-outline-primary me-2">Normal</button>
        <button id="advanceModeBtn" class="btn btn-outline-primary">Advance</button>
      </div>

      <!-- EASY MODE -->
      <div id="easyMode" class="mode">
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
          <button class="btn btn-outline-primary random_number_easy">0</button>
          <button class="btn btn-outline-primary random_number_easy">1</button>
          <button class="btn btn-outline-primary random_number_easy">2</button>
          <button class="btn btn-outline-primary random_number_easy">3</button>
          <button class="btn btn-outline-primary random_number_easy">4</button>
          <button class="btn btn-outline-primary random_number_easy">5</button>
          <button class="btn btn-outline-primary random_number_easy">6</button>
          <button class="btn btn-outline-primary random_number_easy">7</button>
          <button class="btn btn-outline-primary random_number_easy">8</button>
          <button class="btn btn-outline-danger random_number_easy">9</button>
        </div>
        <div class="input-group mb-3 w-50 mx-auto">
          <input id="lastNumberEasy" type="number" class="form-control text-center" placeholder="Last Number" maxlength="1" min="0" max="9" oninput="this.value=this.value.slice(0,1)">
        </div>
        <button id="submitBtnEasy" class="btn btn-primary mb-2">Submit</button>
      </div>

      <!-- NORMAL MODE -->
      <div id="normalMode" class="mode d-none">
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
          <button class="btn btn-outline-danger random_number_normal">0</button>
          <button class="btn btn-outline-primary random_number_normal">1</button>
          <button class="btn btn-outline-primary random_number_normal">2</button>
          <button class="btn btn-outline-primary random_number_normal">3</button>
          <button class="btn btn-outline-primary random_number_normal">4</button>
          <button class="btn btn-outline-primary random_number_normal">5</button>
          <button class="btn btn-outline-primary random_number_normal">6</button>
          <button class="btn btn-outline-primary random_number_normal">7</button>
          <button class="btn btn-outline-primary random_number_normal">8</button>
          <button class="btn btn-outline-danger random_number_normal">9</button>
        </div>
        <div class="container mt-3">
          <div class="row">
            <div class="col-md-6">
              <div class="input-group mb-3 w-75 mx-auto">
                <input id="firstNumberNormal" type="number" class="form-control text-center" placeholder="First Number" maxlength="1" min="0" max="9" oninput="this.value=this.value.slice(0,1)">
              </div>
            </div>
            <div class="col-md-6">
              <div class="input-group mb-3 w-75 mx-auto">
                <input id="lastNumberNormal" type="number" class="form-control text-center" placeholder="Last Number" maxlength="1" min="0" max="9" oninput="this.value=this.value.slice(0,1)">
              </div>
            </div>
          </div>
        </div>
        <button id="submitBtnNormal" class="btn btn-primary mb-2">Submit</button>
      </div>

      <!-- ADVANCE MODE -->
      <div id="advanceMode" class="mode d-none">
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
          <button class="btn btn-outline-danger random_number_advance">0</button>
          <button class="btn btn-outline-primary random_number_advance">1</button>
          <button class="btn btn-outline-primary random_number_advance">2</button>
          <button class="btn btn-outline-primary random_number_advance">3</button>
          <button class="btn btn-outline-danger random_number_advance">4</button>
          <button class="btn btn-outline-primary random_number_advance">5</button>
          <button class="btn btn-outline-primary random_number_advance">6</button>
          <button class="btn btn-outline-primary random_number_advance">7</button>
          <button class="btn btn-outline-danger random_number_advance">8</button>
        </div>
        <div class="container mt-3">
          <div class="row g-2 justify-content-center">
            <div class="col-3 col-md-4">
              <input id="firstNumberAdvance" type="number" class="form-control text-center" placeholder="First Number" maxlength="1" min="0" max="9" oninput="this.value=this.value.slice(0,1)">
            </div>
            <div class="col-3 col-md-4">
              <input id="middleNumberAdvance" type="number" class="form-control text-center" placeholder="Middle Number" maxlength="1" min="0" max="9" oninput="this.value=this.value.slice(0,1)">
            </div>
            <div class="col-3 col-md-4">
              <input id="lastNumberAdvance" type="number" class="form-control text-center" placeholder="Last Number" maxlength="1" min="0" max="9" oninput="this.value=this.value.slice(0,1)">
            </div>
          </div>
          <div class="mt-3 d-flex justify-content-center">
            <button id="submitBtnAdvance" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </div>

      <div id="result" class="mt-2 fw-bold text-secondary"></div>
      <div id="earnings" class="mt-2 fw-bold text-success">💰 Earnings: $<?php echo number_format($earnings, 3); ?></div>
      <h6>Easy: $0.001 | Normal: $0.21 | Advance: $0.5 per guess</h6>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    let earnings = <?php echo $earnings; ?>;

    // MODE TOGGLE
    const easyMode = document.getElementById("easyMode");
    const normalMode = document.getElementById("normalMode");
    const advanceMode = document.getElementById("advanceMode");

    document.getElementById("easyModeBtn").onclick = () => {
      easyMode.classList.remove("d-none");
      normalMode.classList.add("d-none");
      advanceMode.classList.add("d-none");
    };
    document.getElementById("normalModeBtn").onclick = () => {
      easyMode.classList.add("d-none");
      normalMode.classList.remove("d-none");
      advanceMode.classList.add("d-none");
    };
    document.getElementById("advanceModeBtn").onclick = () => {
      easyMode.classList.add("d-none");
      normalMode.classList.add("d-none");
      advanceMode.classList.remove("d-none");
    };

    // RANDOM NUMBER GENERATOR
    function getRandomNumber() {
      return Math.floor(Math.random() * 10);
    }

    // Function to save game result to server
    function saveGameResult(mode, amount, won) {
      const formData = new FormData();
      formData.append('game_result', 'true');
      formData.append('mode', mode);
      formData.append('amount', amount);
      formData.append('won', won);

      fetch('index.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.status !== 'success') {
            console.error('Failed to save game result');
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });
    }

    // EASY MODE
    const buttonsEasy = document.querySelectorAll(".random_number_easy");
    let nextNumbersEasy = [];
    setInterval(() => {
      nextNumbersEasy = [];
      buttonsEasy.forEach(btn => {
        const n = getRandomNumber();
        btn.textContent = n;
        nextNumbersEasy.push(n);
      });
    }, 50);

    buttonsEasy.forEach(btn => {
      btn.addEventListener("click", () => document.getElementById("lastNumberEasy").value = btn.textContent);
    });

    document.getElementById("submitBtnEasy").addEventListener("click", () => {
      const last = document.getElementById("lastNumberEasy").value.trim();
      const resultDiv = document.getElementById("result");
      const earningsDiv = document.getElementById("earnings");

      if (last === "") {
        resultDiv.textContent = "⚠️ Enter the number!";
        resultDiv.classList.replace("text-success", "text-danger");
        return;
      }

      if (parseInt(last) === nextNumbersEasy[nextNumbersEasy.length - 1]) {
        resultDiv.textContent = "✅ Correct Guess!";
        resultDiv.classList.replace("text-danger", "text-success");
        earnings += 0.001; // Easy
        saveGameResult('easy', 0.001, true);
      } else {
        resultDiv.textContent = `❌ Wrong! Next was ${nextNumbersEasy[nextNumbersEasy.length - 1]}`;
        resultDiv.classList.replace("text-success", "text-danger");
        saveGameResult('easy', 0, false);
      }
      earningsDiv.textContent = `💰 Earnings: $${earnings.toFixed(3)}`;
      document.getElementById("lastNumberEasy").value = "";
    });

    // NORMAL MODE
    const buttonsNormal = document.querySelectorAll(".random_number_normal");
    let nextNumbersNormal = [];
    setInterval(() => {
      nextNumbersNormal = [];
      buttonsNormal.forEach(btn => {
        const n = getRandomNumber();
        btn.textContent = n;
        nextNumbersNormal.push(n);
      });
    }, 50);

    buttonsNormal.forEach((btn, index) => {
      btn.addEventListener("click", () => {
        if (index === 0) document.getElementById("firstNumberNormal").value = btn.textContent;
        if (index === buttonsNormal.length - 1) document.getElementById("lastNumberNormal").value = btn.textContent;
      });
    });

    document.getElementById("submitBtnNormal").addEventListener("click", () => {
      const first = document.getElementById("firstNumberNormal").value.trim();
      const last = document.getElementById("lastNumberNormal").value.trim();
      const resultDiv = document.getElementById("result");
      const earningsDiv = document.getElementById("earnings");

      if (first === "" || last === "") {
        resultDiv.textContent = "⚠️ Enter both numbers!";
        resultDiv.classList.replace("text-success", "text-danger");
        return;
      }

      if (parseInt(first) === nextNumbersNormal[0] && parseInt(last) === nextNumbersNormal[nextNumbersNormal.length - 1]) {
        resultDiv.textContent = "✅ Correct Guess!";
        resultDiv.classList.replace("text-danger", "text-success");
        earnings += 0.21; // Normal
        saveGameResult('normal', 0.21, true);
      } else {
        resultDiv.textContent = `❌ Wrong! Next were [${nextNumbersNormal[0]} ... ${nextNumbersNormal[nextNumbersNormal.length - 1]}]`;
        resultDiv.classList.replace("text-success", "text-danger");
        saveGameResult('normal', 0, false);
      }
      earningsDiv.textContent = `💰 Earnings: $${earnings.toFixed(2)}`;
      document.getElementById("firstNumberNormal").value = "";
      document.getElementById("lastNumberNormal").value = "";
    });

    // ADVANCE MODE
    const buttonsAdvance = document.querySelectorAll(".random_number_advance");
    let nextNumbersAdvance = [];
    setInterval(() => {
      nextNumbersAdvance = [];
      buttonsAdvance.forEach(btn => {
        const n = getRandomNumber();
        btn.textContent = n;
        nextNumbersAdvance.push(n);
      });
    }, 50);

    buttonsAdvance.forEach((btn, index) => {
      btn.addEventListener("click", () => {
        if (index === 0) document.getElementById("firstNumberAdvance").value = btn.textContent;
        if (index === 4) document.getElementById("middleNumberAdvance").value = btn.textContent;
        if (index === buttonsAdvance.length - 1) document.getElementById("lastNumberAdvance").value = btn.textContent;
      });
    });

    document.getElementById("submitBtnAdvance").addEventListener("click", () => {
      const first = document.getElementById("firstNumberAdvance").value.trim();
      const middle = document.getElementById("middleNumberAdvance").value.trim();
      const last = document.getElementById("lastNumberAdvance").value.trim();
      const resultDiv = document.getElementById("result");
      const earningsDiv = document.getElementById("earnings");

      if (first === "" || middle === "" || last === "") {
        resultDiv.textContent = "⚠️ Enter all three numbers!";
        resultDiv.classList.replace("text-success", "text-danger");
        return;
      }

      const middleIndex = Math.floor(nextNumbersAdvance.length / 2);
      if (parseInt(first) === nextNumbersAdvance[0] &&
        parseInt(middle) === nextNumbersAdvance[middleIndex] &&
        parseInt(last) === nextNumbersAdvance[nextNumbersAdvance.length - 1]) {
        resultDiv.textContent = "✅ Correct Guess!";
        resultDiv.classList.replace("text-danger", "text-success");
        earnings += 0.5; // Advance
        saveGameResult('advance', 0.5, true);
      } else {
        resultDiv.textContent = `❌ Wrong! Next were [${nextNumbersAdvance[0]} ... ${nextNumbersAdvance[middleIndex]} ... ${nextNumbersAdvance[nextNumbersAdvance.length - 1]}]`;
        resultDiv.classList.replace("text-success", "text-danger");
        saveGameResult('advance', 0, false);
      }
      earningsDiv.textContent = `💰 Earnings: $${earnings.toFixed(2)}`;
      document.getElementById("firstNumberAdvance").value = "";
      document.getElementById("middleNumberAdvance").value = "";
      document.getElementById("lastNumberAdvance").value = "";
    });
  </script>
</body>

</html>
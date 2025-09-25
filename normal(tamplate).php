<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guess</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex justify-content-center align-items-center vh-100 bg-light">

    <div class="card shadow-lg p-4 text-center" style="max-width: 500px; width: 100%;">
        <h3 class="mb-4">🎯 Guess the Next Numbers!</h3>
      
        <!-- Buttons Grid -->
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
            <button class="btn btn-outline-danger random_number">0</button>
            <button class="btn btn-outline-primary random_number">1</button>
            <button class="btn btn-outline-primary random_number">2</button>
            <button class="btn btn-outline-primary random_number">3</button>
            <button class="btn btn-outline-primary random_number">4</button>
            <button class="btn btn-outline-primary random_number">5</button>
            <button class="btn btn-outline-primary random_number">6</button>
            <button class="btn btn-outline-primary random_number">7</button>
            <button class="btn btn-outline-primary random_number">8</button>
            <button class="btn btn-outline-danger random_number">9</button>
        </div>

        <!-- First Input -->
        <div class="container mt-3">
            <div class="row">
                <!-- Left side input -->
                <div class="col-md-6">
                    <div class="input-group mb-3 w-75 mx-auto">
                        <input id="firstNumber" type="number" class="form-control text-center" placeholder="First Number" maxlength="1" min="0" max="9" oninput="this.value=this.value.slice(0,1)">
                    </div>
                </div>

                <!-- Right side input -->
                <div class="col-md-6 d-flex justify-content-end">
                    <div class="input-group mb-3 w-75">
                        <input id="lastNumber" type="number" class="form-control text-center" placeholder="Last Number" maxlength="1" min="0" max="9" oninput="this.value=this.value.slice(0,1)">
                    </div>
                </div>
            </div>
        </div>

        <a href="https://otieu.com/4/8202025" target="_blank" id="submitBtn" class="btn btn-primary">Submit</a>
       <a href="advance.php" class="btn btn-secondary mt-2">Go to Advance</a>
              <a href="easy.php" class="btn btn-secondary mt-2">Go to Easy</a>



        <div id="result" class="mt-2 fw-bold text-secondary"></div>
        <div id="earnings" class="mt-2 fw-bold text-success">💰 Earnings: $0.00</div>
        <h6>0.021$ Per Guess</h6>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const buttons = document.querySelectorAll(".random_number");
        const resultDiv = document.getElementById("result");
        const earningsDiv = document.getElementById("earnings");
        const firstInput = document.getElementById("firstNumber");
        const lastInput = document.getElementById("lastNumber");

        let nextNumbers = []; // store predicted numbers
        let earnings = 0; // user's money

        function getRandomNumber() {
            return Math.floor(Math.random() * 10);
        }

        // Update button numbers every 50 ms
        setInterval(() => {
            nextNumbers = []; // reset prediction list
            buttons.forEach(btn => {
                const nextNum = getRandomNumber();
                btn.textContent = nextNum;
                nextNumbers.push(nextNum);
            });
        }, 50);

        // Click button to fill inputs
        buttons.forEach((btn, index) => {
            btn.addEventListener("click", () => {
                if (index === 0) firstInput.value = btn.textContent;
                if (index === buttons.length - 1) lastInput.value = btn.textContent;
            });
        });

        document.getElementById("submitBtn").addEventListener("click", () => {
            const first = firstInput.value.trim();
            const last = lastInput.value.trim();

            if (first === "" || last === "") {
                resultDiv.textContent = "⚠️ Enter both numbers!";
                resultDiv.classList.replace("text-success", "text-danger");
                return;
            }

            if (parseInt(first) === nextNumbers[0] && parseInt(last) === nextNumbers[nextNumbers.length - 1]) {
                resultDiv.textContent = "✅ Correct Guess!";
                resultDiv.classList.replace("text-danger", "text-success");

                earnings += 0.021;
                earningsDiv.textContent = `💰 Earnings: $${earnings.toFixed(2)}`;
            } else {
                resultDiv.textContent = `❌ Wrong! Next were [${nextNumbers[0]} ... ${nextNumbers[nextNumbers.length - 1]}]`;
                resultDiv.classList.replace("text-success", "text-danger");
            }

            firstInput.value = "";
            lastInput.value = "";
        });
    </script>

    <script>
        // Anti-cheat: disable right-click, F12, Ctrl+Shift+I/J, Ctrl+U, copy/cut/paste outside inputs
        document.addEventListener("contextmenu", e => e.preventDefault());

        document.addEventListener("selectstart", e => {
            if (!["INPUT", "TEXTAREA"].includes(e.target.tagName)) e.preventDefault();
        });

        document.addEventListener("keydown", e => {
            const key = e.key.toLowerCase();
            if (
                key === "f12" ||
                (e.ctrlKey && e.shiftKey && (key === "i" || key === "j")) ||
                (e.ctrlKey && key === "u")
            ) e.preventDefault();

            if (e.ctrlKey && ["c", "x", "v", "p"].includes(key) &&
                !["INPUT", "TEXTAREA"].includes(e.target.tagName)) e.preventDefault();
        });

        ["copy", "cut", "paste"].forEach(evt => {
            document.addEventListener(evt, e => {
                if (!["INPUT", "TEXTAREA"].includes(e.target.tagName)) e.preventDefault();
            });
        });
    </script>

</body>

</html>
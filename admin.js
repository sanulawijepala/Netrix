//Constants
  const socket = new WebSocket('ws://localhost:3000');//server is on localhost port:3000
  const adminpass_request = "adminpassrequest"

  let sign_in = false;

// Send admin password request packet structure, e.g.:
function createAdminPassRequest(password) {
  return JSON.stringify({ type: adminpass_request, password });
}

// Quiz Data (same as index.html)
    let quizData = JSON.parse(localStorage.getItem('quizData')) || {
      math: [],
      science: [],
      ict: []
    };

    // User data
    let users = JSON.parse(localStorage.getItem('users')) || [];
    let userProgress = JSON.parse(localStorage.getItem('userProgress')) || {};
    let currentUser = localStorage.getItem('currentUser') || null;
    let isAdmin = localStorage.getItem('isAdmin') === 'true';

    // DOM Elements
    const adminLogin = document.getElementById('adminLogin');
    const adminPanel = document.getElementById('adminPanel');
    const adminLoginForm = document.getElementById('adminLoginForm');
    const adminPassword = document.getElementById('adminPassword');
    const errorMessage = document.getElementById('errorMessage');
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');
    const quizForm = document.getElementById('quizForm');
    const quizQuestionInput = document.getElementById('quizQuestionInput');
    const quizCategoryInput = document.getElementById('quizCategoryInput');
    const quizDifficultyInput = document.getElementById('quizDifficultyInput');
    const quizOptionsContainer = document.getElementById('quizOptionsContainer');
    const addOptionBtn = document.getElementById('addOptionBtn');
    const quizImageInput = document.getElementById('quizImageInput');
    const quizList = document.getElementById('quizList');
    const leaderboardBody = document.getElementById('leaderboardBody');
    const progressBody = document.getElementById('progressBody');
    const logoutBtn = document.getElementById('logoutBtn');

    // Initialize
    function init() {
      if (isAdmin) {
        showAdminPanel();
      } else {
        adminLogin.style.display = 'block';
        adminPanel.style.display = 'none';
      }
      setupEventListeners();
      loadQuizList();
      loadLeaderboard();
      loadUserProgress();
    }

    // Setup event listeners
    function setupEventListeners() {
      adminLoginForm.addEventListener('submit', handleAdminLogin);
      logoutBtn.addEventListener('click', handleLogout);
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          tabs.forEach(t => t.classList.remove('active'));
          tabContents.forEach(c => c.classList.remove('active'));
          tab.classList.add('active');
          document.getElementById(`${tab.dataset.tab}-tab`).classList.add('active');
        });
      });
      addOptionBtn.addEventListener('click', addOptionField);
      quizForm.addEventListener('submit', handleQuizSubmit);
    }

// Handle admin login
function handleAdminLogin(e) {
  e.preventDefault();

  const password = adminPassword.value.trim();

  if (!password) {
    showError('Please enter a password');
    return;
  }

  // Prepare packet to send
  const packet = JSON.stringify({
    request_data: adminpass_request,
    payload: password
  });

  // Open the socket connection if not already open
  if (socket.readyState === WebSocket.OPEN) {
    socket.send(packet);
  } else {
    socket.addEventListener('open', () => {
      socket.send(packet);
    });
  }

  // Listen for the admin password response only once per login attempt
  socket.addEventListener('adminpassresponse', function onResponse(event) {
    // Remove listener after first response to avoid duplicates
    socket.removeEventListener('adminpassresponse', onResponse);

    const data = event.data;

    // The data received is a JSON string, parse it
    let response;
    try {
      response = JSON.parse(data);
    } catch {
      showError('Invalid response from server');
      return;
    }

    if (response.success === true) {
      isAdmin = true;
      localStorage.setItem('isAdmin', 'true');
      showAdminPanel();
    } else {
      showError('Incorrect password. Please try again.');
    }

    adminPassword.value = '';
  });
}

    // Show error message
    function showError(message) {
      errorMessage.textContent = message;
      errorMessage.style.display = 'block';
      setTimeout(() => {
        errorMessage.style.display = 'none';
      }, 3000);
    }

    // Show admin panel
    function showAdminPanel() {
      adminLogin.style.display = 'none';
      adminPanel.style.display = 'block';
    }

    // Handle logout
    function handleLogout() {
      isAdmin = false;
      localStorage.setItem('isAdmin', 'false');
      adminLogin.style.display = 'block';
      adminPanel.style.display = 'none';
    }

    // Add option field
    function addOptionField() {
      const optionCount = quizOptionsContainer.children.length;
      if (optionCount >= 4) {
        alert('Maximum 4 options allowed');
        return;
      }
      const optionItem = document.createElement('div');
      optionItem.className = 'option-item';
      optionItem.innerHTML = `
        <input type="text" class="form-input" placeholder="Option ${optionCount + 1}" required>
        <input type="radio" name="correctOption" value="${optionCount}" required>
        <button type="button" class="remove-option-btn">
          <i class="fas fa-trash"></i>
        </button>
      `;
      quizOptionsContainer.appendChild(optionItem);
      optionItem.querySelector('.remove-option-btn').addEventListener('click', () => {
        if (quizOptionsContainer.children.length > 2) {
          optionItem.remove();
        } else {
          alert('Minimum 2 options required');
        }
      });
    }

    // Handle quiz form submission
    function handleQuizSubmit(e) {
      e.preventDefault();
      const question = quizQuestionInput.value.trim();
      const category = quizCategoryInput.value;
      const difficulty = quizDifficultyInput.value;
      const image = quizImageInput.value.trim() || null;
      const options = Array.from(quizOptionsContainer.querySelectorAll('input[type="text"]')).map(input => input.value.trim());
      const correctOption = parseInt(quizOptionsContainer.querySelector('input[name="correctOption"]:checked').value);

      if (options.length < 2) {
        alert('At least 2 options are required');
        return;
      }
      if (options.some(opt => !opt)) {
        alert('All options must be filled');
        return;
      }

      const newQuiz = { question, options, answer: correctOption, difficulty, image };
      quizData[category].push(newQuiz);
      localStorage.setItem('quizData', JSON.stringify(quizData));

      quizForm.reset();
      quizOptionsContainer.innerHTML = `
        <div class="option-item">
          <input type="text" class="form-input" placeholder="Option 1" required>
          <input type="radio" name="correctOption" value="0" required>
        </div>
        <div class="option-item">
          <input type="text" class="form-input" placeholder="Option 2" required>
          <input type="radio" name="correctOption" value="1" required>
        </div>
      `;
      loadQuizList();
      alert('Quiz added successfully!');
    }

    // Load quizzes
    function loadQuizList() {
      quizList.innerHTML = '';
      Object.keys(quizData).forEach(category => {
        quizData[category].forEach((quiz, index) => {
          const quizCard = document.createElement('div');
          quizCard.className = 'quiz-card';
          quizCard.innerHTML = `
            <div class="quiz-card-header">
              <div class="quiz-card-title">${quiz.question}</div>
              <div class="quiz-card-meta">
                <span>${getCategoryName(category)}</span>
                <span>${quiz.difficulty.charAt(0).toUpperCase() + quiz.difficulty.slice(1)}</span>
              </div>
            </div>
            <div class="quiz-card-body">
              <div class="quiz-card-actions">
                <button class="quiz-card-btn edit" data-category="${category}" data-index="${index}">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <button class="quiz-card-btn delete" data-category="${category}" data-index="${index}">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </div>
            </div>
          `;
          quizList.appendChild(quizCard);
        });
      });

      quizList.querySelectorAll('.quiz-card-btn.edit').forEach(btn => {
        btn.addEventListener('click', () => editQuiz(btn.dataset.category, btn.dataset.index));
      });
      quizList.querySelectorAll('.quiz-card-btn.delete').forEach(btn => {
        btn.addEventListener('click', () => deleteQuiz(btn.dataset.category, btn.dataset.index));
      });
    }

    // Edit quiz
    function editQuiz(category, index) {
      const quiz = quizData[category][index];
      quizQuestionInput.value = quiz.question;
      quizCategoryInput.value = category;
      quizDifficultyInput.value = quiz.difficulty;
      quizImageInput.value = quiz.image || '';
      quizOptionsContainer.innerHTML = '';
      quiz.options.forEach((option, i) => {
        const optionItem = document.createElement('div');
        optionItem.className = 'option-item';
        optionItem.innerHTML = `
          <input type="text" class="form-input" value="${option}" placeholder="Option ${i + 1}" required>
          <input type="radio" name="correctOption" value="${i}" ${i === quiz.answer ? 'checked' : ''} required>
          <button type="button" class="remove-option-btn">
            <i class="fas fa-trash"></i>
          </button>
        `;
        quizOptionsContainer.appendChild(optionItem);
      });

      quizForm.onsubmit = (e) => {
        e.preventDefault();
        const updatedQuiz = {
          question: quizQuestionInput.value.trim(),
          options: Array.from(quizOptionsContainer.querySelectorAll('input[type="text"]')).map(input => input.value.trim()),
          answer: parseInt(quizOptionsContainer.querySelector('input[name="correctOption"]:checked').value),
          difficulty: quizDifficultyInput.value,
          image: quizImageInput.value.trim() || null
        };
        quizData[category][index] = updatedQuiz;
        localStorage.setItem('quizData', JSON.stringify(quizData));
        quizForm.reset();
        quizForm.onsubmit = handleQuizSubmit;
        quizOptionsContainer.innerHTML = `
          <div class="option-item">
            <input type="text" class="form-input" placeholder="Option 1" required>
            <input type="radio" name="correctOption" value="0" required>
          </div>
          <div class="option-item">
            <input type="text" class="form-input" placeholder="Option 2" required>
            <input type="radio" name="correctOption" value="1" required>
          </div>
        `;
        loadQuizList();
        alert('Quiz updated successfully!');
      };

      quizOptionsContainer.querySelectorAll('.remove-option-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          if (quizOptionsContainer.children.length > 2) {
            btn.parentElement.remove();
          } else {
            alert('Minimum 2 options required');
          }
        });
      });
    }

    // Delete quiz
    function deleteQuiz(category, index) {
      if (confirm('Are you sure you want to delete this quiz?')) {
        quizData[category].splice(index, 1);
        localStorage.setItem('quizData', JSON.stringify(quizData));
        loadQuizList();
        alert('Quiz deleted successfully!');
      }
    }

    // Load leaderboard
    function loadLeaderboard() {
      users.sort((a, b) => b.rewardPoints - a.rewardPoints);
      leaderboardBody.innerHTML = '';
      users.forEach((user, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${index + 1}</td>
          <td>${user.username}</td>
          <td>${user.rewardPoints}</td>
          <td>
            <button class="delete-user-btn" data-username="${user.username}">
              <i class="fas fa-trash"></i> Delete
            </button>
          </td>
        `;
        leaderboardBody.appendChild(row);
      });

      // Add event listeners for delete buttons
      leaderboardBody.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', () => deleteUser(btn.dataset.username));
      });
    }

    // Delete user
    function deleteUser(username) {
      if (confirm(`Are you sure you want to delete user "${username}"?`)) {
        // Remove from users array
        users = users.filter(user => user.username !== username);
        localStorage.setItem('users', JSON.stringify(users));

        // Remove from userProgress
        delete userProgress[username];
        localStorage.setItem('userProgress', JSON.stringify(userProgress));

        // Clear currentUser if deleted
        if (currentUser === username) {
          localStorage.removeItem('currentUser');
          currentUser = null;
        }

        // Refresh tables
        loadLeaderboard();
        loadUserProgress();
        alert(`User "${username}" deleted successfully!`);
      }
    }

    // Load user progress
    function loadUserProgress() {
      progressBody.innerHTML = '';
      Object.keys(userProgress).forEach(username => {
        const progress = userProgress[username];
        const mathPercent = progress.math.total ? ((progress.math.correct / progress.math.total) * 100).toFixed(1) : 0;
        const sciencePercent = progress.science.total ? ((progress.science.correct / progress.science.total) * 100).toFixed(1) : 0;
        const ictPercent = progress.ict.total ? ((progress.ict.correct / progress.ict.total) * 100).toFixed(1) : 0;
        const weakest = Math.min(mathPercent, sciencePercent, ictPercent);
        let improvement = '';
        if (weakest === mathPercent && mathPercent < 70) improvement = 'Mathematics';
        else if (weakest === sciencePercent && sciencePercent < 70) improvement = 'Science';
        else if (weakest === ictPercent && ictPercent < 70) improvement = 'ICT';
        else improvement = 'None';
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${username}</td>
          <td>
            <div class="progress-bar">
              <div class="progress-fill" style="width: ${mathPercent}%"></div>
            </div>
            ${mathPercent}% (${progress.math.correct}/${progress.math.total})
          </td>
          <td>
            <div class="progress-bar">
              <div class="progress-fill" style="width: ${sciencePercent}%"></div>
            </div>
            ${sciencePercent}% (${progress.science.correct}/${progress.science.total})
          </td>
          <td>
            <div class="progress-bar">
              <div class="progress-fill" style="width: ${ictPercent}%"></div>
            </div>
            ${ictPercent}% (${progress.ict.correct}/${progress.ict.total})
          </td>
          <td>${improvement}</td>
        `;
        progressBody.appendChild(row);
      });
    }

    // Get category name
    function getCategoryName(category) {
      const names = { math: 'Mathematics', science: 'Science', ict: 'ICT' };
      return names[category] || category;
    }

    // Initialize
    init();
    // Quiz Data
    let quizData = JSON.parse(localStorage.getItem('quizData')) || {
      math: [
        {
          question: "What is the value of π (pi) rounded to two decimal places?",
          options: ["3.14", "3.16", "3.18", "3.12"],
          answer: 0,
          difficulty: "easy",
          image: null
        },
        {
          question: "Solve for x: 2x + 5 = 15",
          options: ["5", "10", "7.5", "20"],
          answer: 0,
          difficulty: "easy",
          image: null
        },
        {
          question: "What is the area of a triangle with base 6cm and height 4cm?",
          options: ["10cm²", "12cm²", "24cm²", "18cm²"],
          answer: 1,
          difficulty: "medium",
          image: null
        },
        {
          question: "If a right triangle has legs of 3cm and 4cm, what is the length of the hypotenuse?",
          options: ["5cm", "7cm", "6cm", "12cm"],
          answer: 0,
          difficulty: "medium",
          image: null
        },
        {
          question: "What is the derivative of x³ with respect to x?",
          options: ["3x²", "x²", "3x", "x⁴/4"],
          answer: 0,
          difficulty: "hard",
          image: null
        }
      ],
      science: [
        {
          question: "Which planet is known as the Red Planet?",
          options: ["Venus", "Mars", "Jupiter", "Saturn"],
          answer: 1,
          difficulty: "easy",
          image: null
        },
        {
          question: "What is the chemical symbol for gold?",
          options: ["Go", "Gd", "Au", "Ag"],
          answer: 2,
          difficulty: "easy",
          image: null
        },
        {
          question: "Which gas do plants absorb from the atmosphere during photosynthesis?",
          options: ["Oxygen", "Nitrogen", "Carbon Dioxide", "Hydrogen"],
          answer: 2,
          difficulty: "medium",
          image: null
        },
        {
          question: "What is the powerhouse of the cell?",
          options: ["Nucleus", "Mitochondria", "Ribosome", "Endoplasmic Reticulum"],
          answer: 1,
          difficulty: "medium",
          image: null
        },
        {
          question: "Which scientist proposed the theory of relativity?",
          options: ["Isaac Newton", "Albert Einstein", "Galileo Galilei", "Stephen Hawking"],
          answer: 1,
          difficulty: "hard",
          image: null
        }
      ],
      ict: [
        {
          question: "What does HTML stand for?",
          options: [
            "Hyper Text Markup Language",
            "High Tech Modern Language",
            "Hyperlinks and Text Markup Language",
            "Home Tool Markup Language"
          ],
          answer: 0,
          difficulty: "easy",
          image: null
        },
        {
          question: "Which programming language is known as the 'language of the web'?",
          options: ["Python", "Java", "JavaScript", "C++"],
          answer: 2,
          difficulty: "easy",
          image: null
        },
        {
          question: "What is the binary representation of the decimal number 10?",
          options: ["1010", "1001", "1100", "1111"],
          answer: 0,
          difficulty: "medium",
          image: null
        },
        {
          question: "Which protocol is used to send email?",
          options: ["FTP", "HTTP", "SMTP", "SSH"],
          answer: 2,
          difficulty: "medium",
          image: null
        },
        {
          question: "What does API stand for in programming?",
          options: [
            "Automated Programming Interface",
            "Application Programming Interface",
            "Advanced Program Interaction",
            "Application Process Integration"
          ],
          answer: 1,
          difficulty: "hard",
          image: null
        }
      ]
    };

    // Game State
    let currentQuiz = [];
    let currentQuestion = 0;
    let score = 0;
    let selectedOption = null;
    let rewardPoints = parseInt(localStorage.getItem('rewardPoints')) || 0;
    let quizCount = parseInt(localStorage.getItem('quizCount')) || 0;
    
    // DOM Elements
    const rpAmount = document.getElementById('rpAmount');
    const rpProgress = document.getElementById('rpProgress');
    const rpProgressText = document.getElementById('rpProgressText');
    const quizCategory = document.getElementById('quizCategory');
    const quizDifficulty = document.getElementById('quizDifficulty');
    const quizRP = document.getElementById('quizRP');
    const quizQuestion = document.getElementById('quizQuestion');
    const optionsContainer = document.getElementById('optionsContainer');
    const questionCounter = document.getElementById('questionCounter');
    const nextBtn = document.getElementById('nextBtn');
    const resultsModal = document.getElementById('resultsModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalText = document.getElementById('modalText');
    const modalRP = document.getElementById('modalRP');
    const modalBtn = document.getElementById('modalBtn');
    const modalLeaderboardBtn = document.getElementById('modalLeaderboardBtn');
    const modalIcon = document.getElementById('modalIcon');
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
    
    // Initialize the game
    function init() {
      updateRPDisplay();
      startNewQuiz();
      loadQuizList();
      setupEventListeners();
    }
    
    // Setup event listeners
    function setupEventListeners() {
      // Tab switching
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          tabs.forEach(t => t.classList.remove('active'));
          tabContents.forEach(c => c.classList.remove('active'));
          
          tab.classList.add('active');
          document.getElementById(`${tab.dataset.tab}-tab`).classList.add('active');
        });
      });
      
      // Quiz navigation
      nextBtn.addEventListener('click', nextQuestion);
      
      // Results modal
      modalBtn.addEventListener('click', () => {
        resultsModal.classList.remove('show');
        startNewQuiz();
      });
      
      modalLeaderboardBtn.addEventListener('click', () => {
        window.location.href = 'leaderboard.html';
      });
      
      // Admin form
      addOptionBtn.addEventListener('click', addOptionField);
      quizForm.addEventListener('submit', handleQuizSubmit);
    }
    
    // Start a new random quiz
    function startNewQuiz() {
      // Reset quiz state
      currentQuestion = 0;
      score = 0;
      selectedOption = null;
      
      // Select random category
      const categories = ['math', 'science', 'ict'];
      const randomCategory = categories[Math.floor(Math.random() * categories.length)];
      
      // Get 5 random questions from the category
      currentQuiz = [...quizData[randomCategory]].sort(() => 0.5 - Math.random()).slice(0, 5);
      
      // Set category display
      quizCategory.textContent = getCategoryName(randomCategory);
      quizCategory.className = 'quiz-category ' + getCategoryClass(randomCategory);
      
      // Load first question
      loadQuestion();
    }
    
    // Load a question into the UI
    function loadQuestion() {
      const question = currentQuiz[currentQuestion];
      
      // Set question text
      quizQuestion.textContent = question.question;
      
            // Set difficulty and RP value
      const difficultyRP = {
        easy: 10,
        medium: 15,
        hard: 20
      };
      
      quizDifficulty.textContent = `${question.difficulty.charAt(0).toUpperCase() + question.difficulty.slice(1)} Difficulty`;
      quizRP.textContent = `+${difficultyRP[question.difficulty]} RP`;
      
      // Display image if available
      if (question.image) {
        const img = document.createElement('img');
        img.src = question.image;
        img.className = 'quiz-image';
        quizQuestion.insertAdjacentElement('afterend', img);
      }
      
      // Clear previous options
      optionsContainer.innerHTML = '';
      
      // Create and append options
      question.options.forEach((option, index) => {
        const btn = document.createElement('button');
        btn.className = 'option-btn';
        btn.textContent = option;
        btn.dataset.index = index;
        btn.addEventListener('click', () => selectOption(index));
        optionsContainer.appendChild(btn);
      });
      
      // Update question counter
      questionCounter.textContent = `Question ${currentQuestion + 1} of ${currentQuiz.length}`;
      
      // Reset next button
      nextBtn.disabled = true;
      selectedOption = null;
      
      // Remove any previous image if not needed
      const existingImg = quizQuestion.parentElement.querySelector('.quiz-image');
      if (existingImg && !question.image) {
        existingImg.remove();
      }
    }
    
    // Handle option selection
    function selectOption(index) {
      const buttons = optionsContainer.querySelectorAll('.option-btn');
      buttons.forEach(btn => {
        btn.classList.add('disabled');
        if (parseInt(btn.dataset.index) === currentQuiz[currentQuestion].answer) {
          btn.classList.add('correct');
        } else if (parseInt(btn.dataset.index) === index) {
          btn.classList.add('incorrect');
        }
      });
      
      selectedOption = index;
      nextBtn.disabled = false;
      
      // Update score if correct
      if (index === currentQuiz[currentQuestion].answer) {
        score++;
        const rpEarned = {
          easy: 10,
          medium: 15,
          hard: 20
        }[currentQuiz[currentQuestion].difficulty];
        rewardPoints += rpEarned;
        updateRPDisplay();
      }
    }
    
    // Move to next question or show results
    function nextQuestion() {
      currentQuestion++;
      if (currentQuestion < currentQuiz.length) {
        loadQuestion();
      } else {
        showResults();
      }
    }
    
    // Update Reward Points display
    function updateRPDisplay() {
      rpAmount.textContent = rewardPoints;
      const level = Math.floor(rewardPoints / 100) + 1;
      const progress = (rewardPoints % 100);
      rpProgress.style.width = `${progress}%`;
      rpProgressText.textContent = `Level ${level} (${progress}/100)`;
      localStorage.setItem('rewardPoints', rewardPoints);
    }
    
    // Show quiz results
    function showResults() {
      modalTitle.textContent = 'Quiz Completed!';
      modalText.textContent = `You answered ${score} out of ${currentQuiz.length} questions correctly.`;
      modalRP.textContent = `+${score * 10} RP Earned!`;
      modalIcon.className = 'fas fa-trophy modal-icon';
      resultsModal.classList.add('show');
      
      // Update quiz count
      quizCount++;
      localStorage.setItem('quizCount', quizCount);
    }
    
    // Get category name
    function getCategoryName(category) {
      const names = {
        math: 'Mathematics',
        science: 'Science',
        ict: 'ICT'
      };
      return names[category] || category;
    }
    
    // Get category class
    function getCategoryClass(category) {
      return `category-${category}`;
    }
    
    // Add new option field in admin form
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
      
      // Add remove option event listener
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
      
      const newQuiz = {
        question,
        options,
        answer: correctOption,
        difficulty,
        image
      };
      
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
    
    // Load existing quizzes in admin panel
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
              <div class="quiz-card-stats">
                <span>Options: ${quiz.options.length}</span>
                <span>Correct: ${quiz.options[quiz.answer]}</span>
              </div>
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
      
      // Add event listeners for edit and delete buttons
      quizList.querySelectorAll('.quiz-card-btn.edit').forEach(btn => {
        btn.addEventListener('click', () => editQuiz(btn.dataset.category, btn.dataset.index));
      });
      
      quizList.querySelectorAll('.quiz-card-btn.delete').forEach(btn => {
        btn.addEventListener('click', () => deleteQuiz(btn.dataset.category, btn.dataset.index));
      });
    }
    
    // Edit existing quiz
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
      
      // Update form submission to edit instead of add
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
      
      // Add remove option event listeners
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
    
    // Initialize the application
    init();
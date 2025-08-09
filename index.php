<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EduSyncHub | AI-Powered Student Success Platform</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Montserrat:wght@800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <link rel="stylesheet" href="index.css" />
  <style>
    .logout-container {
      position: fixed;
      top: 20px;
      left: 20px;
      z-index: 1000;
    }

    .logout-btn {
      background: linear-gradient(135deg, #ef4444, #f87171);
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .logout-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .logout-btn:active {
      transform: translateY(0);
    }

    .logged-in .cta-buttons {
      display: none !important;
    }

    .welcome-container {
      display: none;
    }

    .logged-in .welcome-container {
      display: block;
    }
  </style>
</head>
<body class="<?php echo $isLoggedIn ? 'logged-in' : ''; ?>">
  <!-- Loading Screen -->
  <div id="loading-screen">
    <div class="loader-container">
      <div class="loader"></div>
      <div class="loader"></div>
      <div class="loader"></div>
    </div>
    <p class="loading-text">Initializing AI-Powered Learning Environment</p>
    <div class="loading-progress">
      <div class="progress-bar"></div>
    </div>
  </div>

  <!-- Theme Toggle -->
  <div class="theme-toggle" id="themeToggle">
    <i class="fas fa-moon"></i>
  </div>

  <!-- Logout Button (shown when logged in) -->
  <?php if ($isLoggedIn): ?>
    <div class="logout-container">
      <button id="logoutBtn" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
      </button>
    </div>
  <?php endif; ?>

  <section class="hero1">
    <div class="hero1-content">
      <div class="mega-logo">
        <span class="logo-icon"><i class="fas fa-graduation-cap"></i></span>
        <h1 class="logo-text">EduSyncHub</h1>
        <p class="logo-tagline">AI-Powered Academic Success</p>
      </div>
    </div>
  </section>

  <!-- Welcome Message Container -->
  <div id="welcomeContainer" class="welcome-container">
    <div class="welcome-message">
      <div class="welcome-content">
        <i class="fas fa-user-graduate welcome-icon"></i>
        <div class="welcome-text">
          <h3>Welcome back, <span id="welcomeUsername"><?php echo htmlspecialchars($username); ?></span>!</h3>
          <p>Ready to continue your learning journey?</p>
        </div>
        <button class="welcome-close">&times;</button>
      </div>
    </div>
  </div>
  <div class="section-gap"></div>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content animate__animated animate__fadeInLeft">
      <h1>Your AI-Powered Academic Companion</h1>
      <p>EduSyncHub revolutionizes learning with smart scheduling, gamified challenges, and collaborative tools designed to maximize your success.</p>
      <div class="cta-buttons">
        <a href="login.php" class="btn btn-primary">
          <i class="fas fa-sign-in-alt"></i> Login
        </a>
        <a href="register.php" class="btn btn-secondary">
          <i class="fas fa-user-plus"></i> Register
        </a>
      </div>

      <div class="timetable-preview animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
        <div class="timetable-header">
          <h3><i class="fas fa-robot"></i> AI-Generated Smart Timetable</h3>
          <div class="timetable-options">
            <button class="option-btn active" id="focusWeak">Focus on Weak Subjects</button>
            <button class="option-btn" id="equalFocus">Balance All Subjects</button>
          </div>
        </div>
        
        <div class="timetable-grid" id="timetableGrid">
          <!-- Timetable will be generated here by JavaScript -->
        </div>
        
        <button class="generate-btn" id="generateTimetable">
          <i class="fas fa-sync-alt"></i> Generate New Timetable
        </button>
      </div>
      <a href="timetable.html" class="generate-btn">Go to page</a>
    </div>
    <img src="resources/f4cf44f8-8fee-4099-baa1-966a1dfb0aa7.svg" alt="Student Learning" class="hero-image animate__animated animate__fadeInRight">
  </section>

  <!-- Features Section -->
  <section class="features">
    <h2 class="section-title animate__animated animate__fadeIn">Why Students Love EduSyncHub</h2>
    <div class="features-grid">
      <!-- ... rest of your feature cards ... -->
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-links">
      <a href="about.html">About</a>
      <a href="contact.html">Contact</a>
      <a href="privacy.html">Privacy</a>
      <a href="terms.html">Terms</a>
    </div>
    <p>© 2025 EduSyncHub | Revolutionizing Education Through AI</p>
  </footer>

  <script>
    // Loading screen fade-out
    window.addEventListener('load', function() {
      setTimeout(function() {
        document.getElementById('loading-screen').classList.add('fade-out');
      }, 2500);
    });

    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    
    if (localStorage.getItem('theme') === 'dark') {
      body.classList.add('dark-mode');
      themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }
    
    themeToggle.addEventListener('click', function() {
      body.classList.toggle('dark-mode');
      
      if (body.classList.contains('dark-mode')) {
        localStorage.setItem('theme', 'dark');
        themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
      } else {
        localStorage.setItem('theme', 'light');
        themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
      }
    });

    // Animate elements when scrolling
    const animateOnScroll = function() {
      const elements = document.querySelectorAll('.animate__animated');
      elements.forEach(el => {
        const elementPosition = el.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        if (elementPosition < windowHeight - 100) {
          const animation = el.classList[1].split('animate__')[1];
          el.classList.add('animate__' + animation);
        }
      });
    };
    
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll();

    // Timetable data and generation
    const subjects = {
      weak: ["Math", "Science", "History"],
      average: ["Sinhala", "Literature", "Business Studies"],
      strong: ["English", "Religion", "ICT"]
    };
    
    const timeSlots = ["9:00-10:30", "11:00-12:30", "14:00-15:30", "16:00-17:30"];
    const days = ["Mon", "Tue", "Wed", "Thu", "Fri"];
    
    function generateTimetable(focusMode) {
      const timetableGrid = document.getElementById('timetableGrid');
      timetableGrid.innerHTML = '';
      
      days.forEach(day => {
        const dayElement = document.createElement('div');
        dayElement.className = 'timetable-day';
        dayElement.textContent = day;
        timetableGrid.appendChild(dayElement);
      });
      
      timeSlots.forEach(slot => {
        days.forEach(day => {
          const slotElement = document.createElement('div');
          slotElement.className = 'timetable-slot';
          
          let subject;
          if (focusMode === 'weak') {
            const rand = Math.random();
            subject = rand < 0.6 ? getRandomSubject('weak') :
                     rand < 0.9 ? getRandomSubject('average') :
                     getRandomSubject('strong');
          } else {
            const allSubjects = [...subjects.weak, ...subjects.average, ...subjects.strong];
            subject = allSubjects[Math.floor(Math.random() * allSubjects.length)];
          }
          
          slotElement.innerHTML = `
            <span class="subject">${subject}</span>
            <span class="time">${slot}</span>
            ${focusMode === 'weak' && subjects.weak.includes(subject) ? 
              '<span class="focus-badge"><i class="fas fa-bolt"></i> Focus</span>' : ''}
          `;
          
          timetableGrid.appendChild(slotElement);
        });
      });
    }
    
    function getRandomSubject(level) {
      const subjectList = subjects[level];
      return subjectList[Math.floor(Math.random() * subjectList.length)];
    }
    
    // Event listeners for timetable
    document.getElementById('focusWeak').addEventListener('click', function() {
      this.classList.add('active');
      document.getElementById('equalFocus').classList.remove('active');
      generateTimetable('weak');
    });
    
    document.getElementById('equalFocus').addEventListener('click', function() {
      this.classList.add('active');
      document.getElementById('focusWeak').classList.remove('active');
      generateTimetable('equal');
    });
    
    document.getElementById('generateTimetable').addEventListener('click', function() {
      const focusMode = document.querySelector('.option-btn.active').id === 'focusWeak' ? 'weak' : 'equal';
      this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
      
      setTimeout(() => {
        generateTimetable(focusMode);
        this.innerHTML = '<i class="fas fa-sync-alt"></i> Generate New Timetable';
      }, 800);
    });
    
    // Initial generation
    generateTimetable('weak');
    
    // Logout functionality
    document.addEventListener('DOMContentLoaded', function() {
      const logoutBtn = document.getElementById('logoutBtn');
      if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
          fetch('logout.php', {
            method: 'POST',
            credentials: 'same-origin'
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              window.location.href = 'index.php';
            }
          })
          .catch(error => console.error('Error:', error));
        });
      }

      // Welcome message handling
      const welcomeContainer = document.getElementById('welcomeContainer');
      if (welcomeContainer) {
        const closeBtn = welcomeContainer.querySelector('.welcome-close');
        if (closeBtn) {
          closeBtn.addEventListener('click', function() {
            welcomeContainer.style.display = 'none';
          });
        }

        // Auto-hide after 8 seconds
        setTimeout(() => {
          if (welcomeContainer.style.display !== 'none') {
            welcomeContainer.style.display = 'none';
          }
        }, 8000);
      }
    });
  </script>
</body>
</html>
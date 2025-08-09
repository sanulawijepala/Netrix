javascript
const express = require('express');
const mysql = require('mysql2/promise');
const cors = require('cors');
const app = express();
const port = 3000;

// Middleware
app.use(cors());
app.use(express.json());

// MySQL connection
const pool = mysql.createPool({
  host: 'localhost',
  user: 'root', // Replace with your MySQL username
  password: '', // Replace with your MySQL password
  database: 'education_portal',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

// API Endpoints
app.post('/api/login', async (req, res) => {
  const { username, password } = req.body;
  try {
    const [rows] = await pool.query('SELECT * FROM users WHERE username = ? AND password = ?', [username, password]);
    if (rows.length > 0) {
      res.json({ success: true, username, rewardPoints: rows[0].rewardPoints });
    } else {
      res.status(401).json({ success: false, message: 'Invalid username or password' });
    }
  } catch (error) {
    console.error('Error during login:', error);
    res.status(500).json({ message: 'Server error' });
  }
});

app.get('/api/user/:username', async (req, res) => {
  const { username } = req.params;
  try {
    const [rows] = await pool.query('SELECT username, rewardPoints FROM users WHERE username = ?', [username]);
    if (rows.length > 0) {
      res.json(rows[0]);
    } else {
      res.status(404).json({ message: 'User not found' });
    }
  } catch (error) {
    console.error('Error fetching user:', error);
    res.status(500).json({ message: 'Server error' });
  }
});

app.get('/api/quizzes', async (req, res) => {
  try {
    const [quizzes] = await pool.query('SELECT * FROM quizzes');
    const quizData = await Promise.all(quizzes.map(async (quiz) => {
      const [options] = await pool.query('SELECT text, isCorrect FROM options WHERE quiz_id = ?', [quiz.id]);
      return { ...quiz, options };
    }));
    res.json(quizData);
  } catch (error) {
    console.error('Error fetching quizzes:', error);
    res.status(500).json({ message: 'Server error' });
  }
});

app.post('/api/quizzes', async (req, res) => {
  const { question, category, difficulty, options, image } = req.body;
  if (!question || options.length < 2 || !options.some(opt => opt.isCorrect)) {
    return res.status(400).json({ message: 'Invalid quiz data' });
  }
  const rewardPoints = difficulty === 'Easy' ? 10 : difficulty === 'Medium' ? 15 : 20;
  try {
    const [result] = await pool.query(
      'INSERT INTO quizzes (question, category, difficulty, rewardPoints, image) VALUES (?, ?, ?, ?, ?)',
      [question, category, difficulty, rewardPoints, image || '']
    );
    const quizId = result.insertId;
    for (const option of options) {
      await pool.query('INSERT INTO options (quiz_id, text, isCorrect) VALUES (?, ?, ?)', [quizId, option.text, option.isCorrect]);
    }
    res.json({ success: true, message: 'Quiz added successfully' });
  } catch (error) {
    console.error('Error adding quiz:', error);
    res.status(500).json({ message: 'Server error' });
  }
});

app.post('/api/update-points', async (req, res) => {
  const { username, points } = req.body;
  try {
    const [result] = await pool.query(
      'UPDATE users SET rewardPoints = rewardPoints + ? WHERE username = ?',
      [points, username]
    );
    if (result.affectedRows > 0) {
      const [user] = await pool.query('SELECT rewardPoints FROM users WHERE username = ?', [username]);
      res.json({ success: true, rewardPoints: user[0].rewardPoints });
    } else {
      res.status(404).json({ message: 'User not found' });
    }
  } catch (error) {
    console.error('Error updating points:', error);
    res.status(500).json({ message: 'Server error' });
  }
});

app.get('/api/leaderboard', async (req, res) => {
  try {
    const [users] = await pool.query('SELECT username, rewardPoints FROM users ORDER BY rewardPoints DESC');
    res.json(users);
  } catch (error) {
    console.error('Error fetching leaderboard:', error);
    res.status(500).json({ message: 'Server error' });
  }
});

// Start server
app.listen(port, () => {
  console.log(`Server running at http://localhost:${port}`);
});
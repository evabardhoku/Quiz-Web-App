# Quiz Platform (UniQuiz) – Web Application

A web-based quiz platform that supports two roles: **Professor** and **Student**.
Professors can create and manage courses, topics, questions, and lecture PDFs, while students can enroll in courses, take quizzes by difficulty, track progress, and generate random practice quizzes.

## Key Features

### Professor
- **Account management**: register, login/logout, update profile info and password
- **Course management**: create, view, and delete courses
- **Topic management**: create, edit, and delete topics (organized under courses)
- **Question management**: add/edit/delete MCQ questions with difficulty levels (Easy / Intermediate / Hard)
- **Lecture upload**: upload and manage PDF lecture materials per topic
- **Student performance tracking**:
  - see student activity (completed quizzes)
  - view average scores by difficulty
  - view full quiz attempts (detailed results)

### Student
- **Account management**: register, login/logout, profile updates
- **Course enrollment**: browse available courses and enroll
- **Quiz taking**:
  - access quizzes by course/topic
  - choose difficulty (Easy / Intermediate / Hard)
  - submit quiz and get instant score
- **Progress dashboard**: track learning progress across courses/topics/difficulty levels
- **Random quiz generation**: generate a unique practice quiz on demand
- **Lecture access**: open/download lecture PDFs provided by professors

## How It Works (High-Level)

1. Professors create courses → topics → questions (+ optional lecture PDFs).
2. Students enroll in courses and take quizzes per topic & difficulty.
3. The platform calculates scores instantly and stores progress.
4. Professors can view analytics and detailed attempts for enrolled students.

   <img width="923" height="410" alt="image" src="https://github.com/user-attachments/assets/e10032ce-669c-40d0-8f03-03bdc4c7c3b0" />


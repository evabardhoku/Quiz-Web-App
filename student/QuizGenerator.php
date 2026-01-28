<?php
class QuizGenerator {
    private $pdo;
    private $studentId;
    private $debug;

    public function __construct($pdo, $studentId, $debug = true) {
        $this->pdo = $pdo;
        $this->studentId = $studentId;
        $this->debug = $debug;
    }

    private function assignWeight($level, $answered) {
        return match ($level) {
            1 => $answered ? 1 : 3,
            2 => $answered ? 2 : 5,
            3 => $answered ? 3 : 7,
            default => 1,
        };
    }

    public function generateQuiz($count = 20) {

        $questions = $this->fetchQuestionsByStudentClasses($this->studentId);

        if (empty($questions)) {
            $this->log("❌ No questions found for student_id = {$this->studentId}");
            return [];
        }

        // Assign weights
        foreach ($questions as &$q) {
            $q['weight'] = $this->assignWeight($q['level'] ?? 1, false);
        }

        // Select weighted questions
        $selected = $this->weightedRandomSample($questions, $count);

        // 🔧 Load answers for selected questions
        $questionIds = array_column($selected, 'id');
        $answersMap = $this->fetchAnswersForQuestions($questionIds);

        foreach ($selected as &$q) {
            $q['answers'] = $answersMap[$q['id']] ?? [];
        }

        return $selected;
    }


    private function fetchAnswersForQuestions(array $questionIds): array {
        if (empty($questionIds)) return [];

        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM answers WHERE question_id IN ($placeholders)");
            $stmt->execute($questionIds);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $answersMap = [];
            foreach ($rows as $row) {
                $answersMap[$row['question_id']][] = $row;
            }

            return $answersMap;
        } catch (PDOException $e) {
            $this->log("Error fetching answers: " . $e->getMessage());
            return [];
        }
    }


    private function fetchQuestionsByStudentClasses($studentId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT q.*
                FROM student_classes sc
                JOIN classes c ON sc.class_id = c.id
                JOIN quizzes z ON z.class_id = c.id
                JOIN questions q ON q.quiz_id = z.id
                WHERE sc.student_id = ?
            ");
            $stmt->execute([$studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->log("Error fetching questions: " . $e->getMessage());
            return [];
        }
    }

//    private function fetchAnswersForQuestion($questionId) {
//        try {
//            $stmt = $this->pdo->prepare("SELECT * FROM answers WHERE question_id = ?");
//            $stmt->execute([$questionId]);
//            return $stmt->fetchAll(PDO::FETCH_ASSOC);
//        } catch (PDOException $e) {
//            $this->log("Error fetching answers for question $questionId: " . $e->getMessage());
//            return [];
//        }
//    }

    private function weightedRandomSample($questions, $count) {
        $selected = [];

        while (count($selected) < $count && count($questions) > 0) {
            $totalWeight = array_sum(array_column($questions, 'weight'));
            if ($totalWeight == 0) break;

            $rand = mt_rand() / mt_getrandmax() * $totalWeight;
            $sum = 0;

            foreach ($questions as $i => $q) {
                $sum += $q['weight'];
                if ($sum >= $rand) {
                    $selected[] = $q;
                    unset($questions[$i]);
                    $questions = array_values($questions);
                    break;
                }
            }
        }

        return $selected;
    }

    private function log($message, $data = null) {
        if (!$this->debug) return;
        echo "<pre>";
        echo is_string($message) ? $message . "\n" : print_r($message, true);
        if ($data !== null) print_r($data);
        echo "</pre>";
    }
}

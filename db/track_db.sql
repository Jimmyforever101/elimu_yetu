-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2026 at 02:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `track_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `course_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `profile_pic`, `role`, `course_id`) VALUES
(1, 'JIMMY', 'admin@gmail.com', '$2y$10$kr0e8MtXs85erWJ4k/ZotuOMqT2L.pALyz.un8UxUBjERbz4VySx2', '../assets/images/default_profile.png', 'admin', NULL),
(4, 'course', 'course@gmail.com', '$2y$10$uGnlGbZCR/w8ycPekycju.Bah5wzEggSvAdFDN.86oDNs/35Bvhim', '../assets/images/default_profile.png', 'course_admin', 19);

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `criteria` varchar(255) DEFAULT NULL,
  `icon_url` varchar(255) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`) VALUES
(20, 'PROGRAMMING', 'Learning fundamentals of computer programing!'),
(24, 'APPLICATION', 'Learning fundamentals of computer application!'),
(25, 'NETWORKING', 'learning fundamentals of a computer networking!'),
(26, 'IOT&ROBOTICS', 'learning!');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`student_id`, `course_id`, `enrolled_at`) VALUES
(6, 20, '2025-08-20 10:10:28'),
(9, 20, '2025-08-20 20:25:45'),
(9, 24, '2025-08-30 11:23:46'),
(9, 25, '2025-08-30 11:34:35'),
(9, 26, '2025-09-15 10:01:10');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `content_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `course_id`, `title`, `content`, `content_url`) VALUES
(23, 20, 'programming', '<p>contents...</p>', NULL),
(26, 25, 'Networking', '<p>contents...<p>', NULL),
(27, 25, 'Networking', '<p>contents...<p>', NULL),
(28, 24, 'application', 'contents', NULL),
(29, 25, 'Networking', 'networking', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `student_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `status` enum('Not Started','In Progress','Completed') DEFAULT 'Not Started',
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress`
--

INSERT INTO `progress` (`student_id`, `lesson_id`, `status`, `timestamp`) VALUES
(9, 23, 'Completed', '2025-08-20 20:25:47'),
(9, 26, 'Completed', '2025-08-30 11:34:37'),
(9, 27, 'Completed', '2026-01-04 13:59:11'),
(9, 28, 'Completed', '2025-08-30 11:33:35');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `questions` text DEFAULT NULL,
  `answers` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `lesson_id`, `title`, `questions`, `answers`) VALUES
(30, 23, 'php intro quiz', NULL, NULL),
(31, 28, 'application', NULL, NULL),
(32, 26, 'Networking', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `option_1` varchar(255) NOT NULL,
  `option_2` varchar(255) NOT NULL,
  `option_3` varchar(255) NOT NULL,
  `option_4` varchar(255) NOT NULL,
  `correct_option` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `quiz_id`, `question`, `option_1`, `option_2`, `option_3`, `option_4`, `correct_option`) VALUES
(60, 30, '0.php is a programing Language!', 'yes', 'no', '-', '-', 1),
(61, 30, '1. PHP originally stood for \"Personal Home Page\".', 'yes', 'no', '-', '-', 1),
(62, 30, '2. PHP was created by Rasmus Lerdorf in 1994.', 'yes', 'no', '-', '-', 1),
(63, 30, '3. PHP is a client-side scripting language.', 'yes', 'no', '-', '-', 2),
(64, 30, '4. PHP files typically use the .php extension.', 'yes', 'no', '-', '-', 1),
(65, 30, '5. PHP variables start with the @ symbol.', 'yes', 'no', '-', '-', 2),
(66, 30, '6. Variable names in PHP are case-sensitive.', 'yes', 'no', '-', '-', 1),
(67, 30, '7. PHP code can be embedded directly into HTML.', 'yes', 'no', '-', '-', 1),
(68, 30, '8. You declare variables in PHP using the keyword var.', 'yes', 'no', '-', '-', 2),
(69, 30, '9. PHP is loosely typed, so you don’t need to declare variable types.', 'yes', 'no', '-', '-', 1),
(71, 31, 'php is a programing Language!', 'yes', 'no', '-', '-', 1),
(72, 32, 'php is a programing Language!', 'yes', 'no', '-', '-', 1),
(73, 30, 'php is a programing Language!', 'yes', 'no', '3', '4', 1);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `student_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `attempts` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_results`
--

INSERT INTO `quiz_results` (`student_id`, `quiz_id`, `score`, `attempts`) VALUES
(9, 30, 82, 1),
(9, 31, 100, 1);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `email`, `phone`, `profile_pic`, `password`) VALUES
(6, 'willy', 'willy@gmail.com', '0743725594', '', '$2y$10$3yLLU6xjuWD4geOPrBF0duVYjKLIVS4DkIdGBvu1RZFI8zgV1OYUa'),
(9, 'admin', 'admin@gmail.com', '0767711890', NULL, '$2y$10$Ue2fqi49JFbJq.k1eMB.iubRPdmn0mtybx99fbiBMnS26ismS1eD6');

-- --------------------------------------------------------

--
-- Table structure for table `student_badges`
--

CREATE TABLE `student_badges` (
  `student_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `date_awarded` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------


--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`student_id`,`lesson_id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`student_id`,`quiz_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `student_badges`
--
ALTER TABLE `student_badges`
  ADD PRIMARY KEY (`student_id`,`badge_id`),
  ADD KEY `badge_id` (`badge_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `badges`
--
ALTER TABLE `badges`
  ADD CONSTRAINT `badges_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progress_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_results_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_badges`
--
ALTER TABLE `student_badges`
  ADD CONSTRAINT `student_badges_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

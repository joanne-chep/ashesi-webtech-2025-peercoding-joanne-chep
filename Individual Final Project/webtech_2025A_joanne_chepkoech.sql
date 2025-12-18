-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 18, 2025 at 01:33 PM
-- Server version: 8.0.44-0ubuntu0.24.04.2
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webtech_2025A_joanne_chepkoech`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `therapist_id` int NOT NULL,
  `appointment_date` datetime NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `session_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `session_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` char(1) NOT NULL DEFAULT 'P',
  `attended_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`session_id`, `user_id`, `status`, `attended_at`) VALUES
(1, 8, 'P', '2025-11-22 23:11:54'),
(2, 8, 'L', '2025-11-22 23:11:54'),
(2, 12, 'P', '2025-12-03 18:33:26'),
(3, 8, 'A', '2025-11-22 23:11:54'),
(6, 12, 'P', '2025-12-03 18:31:25'),
(6, 17, 'L', '2025-12-03 18:31:25');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `time` varchar(10) NOT NULL,
  `session_type` varchar(100) DEFAULT 'General Session',
  `price` decimal(10,2) DEFAULT '0.00',
  `duration` int DEFAULT '60',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `therapist_id` int DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `date`, `time`, `session_type`, `price`, `duration`, `created_at`, `therapist_id`, `status`) VALUES
(6, 1, '2025-12-08', '09:00', 'Child Therapy', 65.00, 60, '2025-12-08 01:12:17', 4, 'Confirmed'),
(7, 1, '2025-12-09', '09:00', 'Couples', 35.00, 60, '2025-12-08 02:42:08', 2, 'Confirmed'),
(8, 1, '2025-12-31', '09:00', 'Couples', 35.00, 60, '2025-12-08 08:27:56', 2, 'Confirmed'),
(9, 1, '2025-12-12', '09:00', 'Couples', 35.00, 60, '2025-12-11 15:37:06', 2, 'Cancelled'),
(10, 2, '2026-01-02', '09:00', 'Couples', 35.00, 60, '2025-12-11 16:10:30', 2, 'Confirmed'),
(11, 2, '2025-12-12', '09:00', 'Child Therapy', 65.00, 60, '2025-12-11 16:11:37', 4, 'Cancelled'),
(12, 6, '2025-12-25', '09:00', 'Child/Teen Therapy', 65.00, 60, '2025-12-11 23:23:16', 4, 'Confirmed'),
(13, 6, '2025-12-25', '09:00', 'Child/Teen Therapy', 65.00, 60, '2025-12-11 23:42:18', 4, 'Confirmed'),
(14, 7, '2026-01-01', '09:00', 'Child/Teen Therapy', 65.00, 60, '2025-12-12 17:43:35', 4, 'Cancelled'),
(15, 7, '2026-01-02', '15:00', 'Adult Therapy', 65.00, 60, '2025-12-12 17:52:18', 2, 'Confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int NOT NULL,
  `course_code` varchar(10) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `fi_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `course_name`, `fi_id`, `created_at`) VALUES
(1, 'CS 101', 'Introduction to Computing', 6, '2025-11-22 23:01:58'),
(2, 'CS 205', 'Data Structures', 6, '2025-11-22 23:01:58'),
(3, 'CS 304', 'Web Technologies', 6, '2025-11-22 23:01:58'),
(4, 'CSS 313', 'Machine Learning', 6, '2025-11-23 00:58:33'),
(5, 'CSS 612', 'FDE', 9, '2025-11-23 02:35:28'),
(6, 'ART413', 'Art and Design', 9, '2025-11-23 02:35:49'),
(7, 'IN 568', 'Intro To AI', 9, '2025-11-23 02:36:13'),
(8, 'cs 133', 'Database Management System', 14, '2025-11-23 22:24:00'),
(9, 'CS 122', 'AI', 14, '2025-11-23 22:26:38');

-- --------------------------------------------------------

--
-- Table structure for table `course_enrollments`
--

CREATE TABLE `course_enrollments` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `student_id` int NOT NULL,
  `enrolled_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `course_enrollments`
--

INSERT INTO `course_enrollments` (`id`, `course_id`, `student_id`, `enrolled_at`) VALUES
(1, 1, 12, '2025-11-23 02:33:44'),
(2, 3, 12, '2025-11-23 02:33:52'),
(3, 5, 13, '2025-11-23 02:44:15'),
(4, 2, 13, '2025-11-23 21:56:57'),
(5, 6, 13, '2025-11-23 22:07:00'),
(6, 8, 15, '2025-11-23 22:26:11'),
(7, 1, 17, '2025-12-03 17:45:27');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `student_id` int NOT NULL,
  `request_status` varchar(10) NOT NULL DEFAULT 'Pending',
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `course_id`, `student_id`, `request_status`, `requested_at`) VALUES
(1, 1, 12, 'Approved', '2025-11-23 02:19:17'),
(2, 3, 12, 'Approved', '2025-11-23 02:19:37'),
(3, 5, 13, 'Approved', '2025-11-23 02:38:54'),
(4, 2, 13, 'Approved', '2025-11-23 02:39:09'),
(5, 5, 12, 'Rejected', '2025-11-23 02:41:50'),
(6, 6, 13, 'Approved', '2025-11-23 22:06:20'),
(7, 8, 15, 'Approved', '2025-11-23 22:25:33'),
(8, 9, 15, 'Rejected', '2025-11-23 22:27:03'),
(9, 1, 17, 'Approved', '2025-12-03 14:51:46'),
(10, 9, 17, 'Pending', '2025-12-03 18:05:04');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('book','podcast','video','article') NOT NULL,
  `link_url` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `title`, `type`, `link_url`, `description`, `created_at`) VALUES
(1, 'Mindfulness for Beginners', 'article', 'https://www.mindful.org/meditation/mindfulness-getting-started/', 'A simple guide to grounding yourself in the present moment.', '2025-12-07 01:10:03'),
(2, 'The Power of Vulnerability', 'video', 'https://www.ted.com/talks/brene_brown_the_power_of_vulnerability', 'Brené Brown explores human connection and the courage to be imperfect.', '2025-12-07 01:10:03'),
(3, 'Sleep Hygiene 101', 'article', 'https://www.sleepfoundation.org/sleep-hygiene', 'Practical tips to improve your sleep quality and mental health.', '2025-12-07 01:10:03'),
(4, 'Dealing with Anxiety', 'podcast', 'https://www.npr.org/life-kit/topics/mental-health', 'Expert advice on managing anxiety in stressful times.', '2025-12-07 01:10:03'),
(5, 'Understanding Depression', 'article', 'https://www.nimh.nih.gov/health/topics/depression', 'Clear, medical explanations of symptoms and treatments.', '2025-12-07 01:10:03'),
(6, 'Be The Pond | Mindfulness for Kids', 'video', 'https://www.youtube.com/watch?v=wf5K3pP2IUQ', 'An engaging 5-minute animated video that teaches children how to deal with big emotions by imagining their mind is a pond and their feelings are just fish swimming by. Great for anxiety and emotional regulation.', '2025-12-17 17:59:53');

-- --------------------------------------------------------

--
-- Table structure for table `saved_resources`
--

CREATE TABLE `saved_resources` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `resource_id` int NOT NULL,
  `saved_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `saved_resources`
--

INSERT INTO `saved_resources` (`id`, `user_id`, `resource_id`, `saved_at`) VALUES
(4, 1, 3, '2025-12-07 02:27:26'),
(5, 1, 1, '2025-12-07 02:46:08'),
(6, 5, 3, '2025-12-08 08:20:11'),
(7, 6, 2, '2025-12-11 23:23:44'),
(8, 7, 2, '2025-12-12 17:47:58');

-- --------------------------------------------------------

--
-- Table structure for table `serenity_users`
--

CREATE TABLE `serenity_users` (
  `id` int NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('client','therapist','admin') DEFAULT 'client',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_image` varchar(500) DEFAULT 'https://i.pinimg.com/736x/8b/16/7a/8b167af653c2399dd93b952a48740620.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `serenity_users`
--

INSERT INTO `serenity_users` (`id`, `full_name`, `email`, `password_hash`, `role`, `created_at`, `profile_image`) VALUES
(1, 'Joanne Chepkoech', 'joannechepkoech004@gmail.com', '$2y$10$C5vRM82po1v0XWuipigKRe6T8bWOQkbtCm2c9vjS7aVLStHvjbGyS', 'client', '2025-12-06 23:21:48', 'https://i.pinimg.com/1200x/42/f6/2b/42f62b65a8d3348381fc352f1d6da71d.jpg'),
(2, 'Marie Johns', 'mariejohns@gmail.com', '$2y$10$iDNxpruTPP7O5qbQ6j8ShOmsE2wLwJ212.XTRTFZcLpCydQeDNWny', 'therapist', '2025-12-06 23:40:45', 'https://i.pinimg.com/736x/8b/16/7a/8b167af653c2399dd93b952a48740620.jpg'),
(3, 'Joanne Admin', 'admin@serenity.com', '$2y$10$0PWNuJZnZYYaz0ovK3/BuePxIFxmKgEL7JOAhmcRnjesdBK6paoPi', 'admin', '2025-12-06 23:56:44', 'https://i.pinimg.com/736x/b5/87/66/b5876617ab1ca0d46ec3a8c375626e20.jpg'),
(4, 'Joyce Chepkemoi', 'joycechepkemoi822@gmail.com', '$2y$10$OwSkn9f9nk/4tAHbHBYah.zMiimd3T1jPymNWodYJEek7dKcXn6pK', 'therapist', '2025-12-08 00:45:30', 'https://i.pinimg.com/1200x/01/ab/fb/01abfbed71331edaabb34dd1770853bb.jpg'),
(5, 'Faith Chepkemoi', 'faithchepkemoi@gmail.com', '$2y$10$RnfXWMP/QgITU7S0DXEPhe4U9/NJConnsvOWpSHwLMp1tW.if3Bh6', 'client', '2025-12-08 08:19:40', 'https://i.pinimg.com/736x/8b/16/7a/8b167af653c2399dd93b952a48740620.jpg'),
(6, 'Elsa Akinyi', 'elsa@gmail.com', '$2y$10$v/Mkwq5rN8l32RA2s/PEtOKI3laXs.lzcq.2ga.3vleKDSwW1ZDje', 'client', '2025-12-11 23:22:40', 'https://i.pinimg.com/736x/8b/16/7a/8b167af653c2399dd93b952a48740620.jpg'),
(7, 'Francis Theuri Karimi', 'francis@gmail.com', '$2y$10$np8NCndBDW0HtnQpDjaQ4uktkcTDy3ah8t1eKF4knC/lj5bWKq1gC', 'client', '2025-12-12 17:41:31', 'https://i.pinimg.com/1200x/27/32/66/273266bec3e112d99c077ed7a4b505af.jpg'),
(8, 'Mary Gorety', 'marygorety@gmail.com', '$2y$10$DAbd1F4Z6.BH765aAQ2XmOlxLDg2I/tMMi3LWxmqJ7iuPHd4Wp6ea', 'therapist', '2025-12-17 12:00:06', 'https://i.pinimg.com/736x/8b/16/7a/8b167af653c2399dd93b952a48740620.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `session_title` varchar(100) NOT NULL,
  `attendance_code` varchar(10) NOT NULL,
  `session_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `course_id`, `session_title`, `attendance_code`, `session_date`, `start_time`, `end_time`, `created_at`) VALUES
(1, 3, 'Week 1 - Attendance Demo', '040e', '2025-11-25', '09:00:00', '10:30:00', '2025-11-22 23:07:12'),
(2, 3, 'Week 2 - Lab Participation', '31a2', '2025-12-02', '09:00:00', '10:30:00', '2025-11-22 23:07:12'),
(3, 1, 'Week 1 - Lab Setup', 'b21d', '2025-11-26', '14:00:00', '16:00:00', '2025-11-22 23:07:12'),
(4, 5, 'Week 1 Lecture', '3ae0', '2025-09-04', '10:04:00', '10:05:00', '2025-11-23 22:05:13'),
(5, 6, 'Week 1 Lecture', '66cf', '2025-09-04', '11:55:00', '13:15:00', '2025-11-23 22:08:54'),
(6, 1, 'Python Basics', '1A7E', '2025-12-18', '10:48:00', '11:48:00', '2025-12-03 17:48:46');

-- --------------------------------------------------------

--
-- Table structure for table `therapist_profiles`
--

CREATE TABLE `therapist_profiles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `bio` text,
  `verification_status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `therapist_profiles`
--

INSERT INTO `therapist_profiles` (`id`, `user_id`, `specialization`, `city`, `hourly_rate`, `bio`, `verification_status`) VALUES
(1, 2, 'Couples', 'Nairobi', 65.00, 'Marie Johns is a certified couples therapist who helps partners improve communication, manage conflict, and build stronger emotional connections. She provides a safe and supportive space for couples to express themselves openly and work towards healthier relationships.', 'approved'),
(2, 4, 'Child Therapy', 'Kajiado', 65.00, 'Worked with kids for 20 years.', 'approved'),
(3, 8, 'Child Therapy', 'Online', 35.00, 'I am a licensed child therapist who is passionate about supporting children through their emotional, social, and behavioral challenges. I provide a safe and welcoming space where children can express their feelings freely and feel understood.\r\n\r\nI work with children facing anxiety, trauma, low self-esteem, family changes, and difficulties at school. My approach is gentle, patient, and child-centered, using age-appropriate techniques such as play therapy, storytelling, and creative activities to help children communicate and heal.', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL COMMENT 'Unique ID for each user',
  `name` varchar(100) NOT NULL COMMENT 'User’s full name',
  `email` varchar(100) NOT NULL COMMENT 'User’s email address',
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(6, 'Joanne Chepkoech', 'joannechepkoech004@gmail.com', '$2y$10$A0QWF3YGf56ysxaixkdc2uyjLW0ZdceKTc3EVlCFYSIq0WQc/j2Xe', 'fi'),
(7, 'Mary', 'mary@gmail', '$2y$10$bU7LEuBz600sqASp2WZAFe2zGERGAfZdH3yKdT.IdramzHp8htp82', 'fi'),
(8, 'Mary Achieng', 'mary68@gmail.com', '$2y$10$LrolxO4X3SaiT/Sz8kjp3.fZgW8wnNWYUQ.6Rrd81KmmP4jkwHcgm', 'other'),
(9, 'Gorety Atieno', 'gorety@gmail.com', '$2y$10$25MKBKPACjuHfdrnV2xqaOQemuX28HTzBq.FQCJ5GnaWsfKOidfle', 'fi'),
(10, 'Anne', 'Anne@gmail.com', '$2y$10$DtrpcSO4wZ0kJzoLnEAicubo7974LB.qtYzBloT9D15CzIV8vrp5m', 'other'),
(11, 'Joyce Chepkemoi', 'joycechepkemoi822@gmail.com', '$2y$10$uLFvJ6YCfnOY65qFPcwlduPsbqcCcw6tzQXChmqbvo29qUxLEq4qm', 'fi'),
(12, 'Marie Chemutai', 'marie@gmail.com', '$2y$10$eu.oDb4bFUvf5mMKZFpfnei4GjLZ728bRXDAaq6A5t5Uxh3HaYO4q', 'student'),
(13, 'Jane Njeri', 'jane@gmail.com', '$2y$10$z7UPBWgG.s2YHv4tgoMpnu0FYqBGPyzWt/laMObdg0p7sdVAZEEvu', 'student'),
(14, 'Peter Korir', 'peter@gmail.com', '$2y$10$p2TY7hhPAaEMpLDsWKimv.WArUflrHrZKrwOlPS/gKeDjl8oD.DjO', 'fi'),
(15, 'Cindy Wanyika', 'cindy@gmail.com', '$2y$10$b34aft5WpcM0bcuzJJzsOezowCUIDgFXCgsxuL0z.KaGj39TS36Sa', 'student'),
(16, 'Cindy ', 'cindy@email', '1234', 'fi'),
(17, 'Faith Chepkemoi', 'faith@gmail.com', '$2y$10$XqpM1iSo89ju246.JqHeIOPN986U7Q2L/Kjyzj1pW1j1yKA3AXOYG', 'student'),
(18, 'Malaika', 'malaika@gmail.com', '$2y$10$lEGRXZ2RhYwu5bHf4Tq6Wef227XunbShbulwZ3.Jab.MO4cDecCQW', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `therapist_id` (`therapist_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`session_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `fi_id` (`fi_id`);

--
-- Indexes for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`course_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_request` (`course_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saved_resources`
--
ALTER TABLE `saved_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `serenity_users`
--
ALTER TABLE `serenity_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `therapist_profiles`
--
ALTER TABLE `therapist_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `saved_resources`
--
ALTER TABLE `saved_resources`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `serenity_users`
--
ALTER TABLE `serenity_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `therapist_profiles`
--
ALTER TABLE `therapist_profiles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for each user', AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `serenity_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`therapist_id`) REFERENCES `serenity_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `serenity_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`fi_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD CONSTRAINT `course_enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_enrollments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_resources`
--
ALTER TABLE `saved_resources`
  ADD CONSTRAINT `saved_resources_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `serenity_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_resources_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `therapist_profiles`
--
ALTER TABLE `therapist_profiles`
  ADD CONSTRAINT `therapist_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `serenity_users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =========================================================================
-- DATABASE CONFIGURATION & MOCK DATA SCRIPT (XAMPP / MySQL / MariaDB)
-- Project Group: GR07
-- File Name: mmdb_gr07_mock_data.sql
-- Description: Creates schemas and inserts structured mock data for evaluation 
--              of Attribute-Based Retrieval (ABR), Text-Based Retrieval (TBR),
--              and Content-Based Retrieval (CBR) metrics.
-- =========================================================================

-- =========================================================================
-- 1. DROP EXISTING TABLES (Ordered sequentially to respect foreign keys)
-- =========================================================================
DROP TABLE IF EXISTS FACIAL_EXPRESSION_ANALYSIS;
DROP TABLE IF EXISTS MULTIMEDIA_CONTENT;
DROP TABLE IF EXISTS SUBMISSION;
DROP TABLE IF EXISTS ASSIGNMENT;
DROP TABLE IF EXISTS STUDENT;

-- =========================================================================
-- 2. TABLE CREATION SECTION
-- =========================================================================

-- Core User Profile
CREATE TABLE STUDENT (
    student_id          VARCHAR(50) NOT NULL,
    student_name        VARCHAR(100) NOT NULL,
    student_email       VARCHAR(100) NOT NULL,
    life_motto          VARCHAR(255),
    profile_image_path  VARCHAR(500),
    CONSTRAINT pk_student PRIMARY KEY (student_id)
);

-- Task Constraints
CREATE TABLE ASSIGNMENT (
    assignment_id       INT NOT NULL,
    assignment_title    VARCHAR(150) NOT NULL,
    due_date            DATETIME NOT NULL,
    max_file_size       INT NOT NULL,
    CONSTRAINT pk_assignment PRIMARY KEY (assignment_id)
);

-- Central Bridge & ABR Logic Target
CREATE TABLE SUBMISSION (
    submission_id       INT NOT NULL,
    student_id          VARCHAR(50) NOT NULL,
    assignment_id       INT NOT NULL,
    submission_date     DATETIME NOT NULL,
    file_size           INT NOT NULL,
    abr_status          VARCHAR(50),
    CONSTRAINT pk_submission PRIMARY KEY (submission_id),
    CONSTRAINT fk_submission_student FOREIGN KEY (student_id) REFERENCES STUDENT(student_id) ON DELETE CASCADE,
    CONSTRAINT fk_submission_assignment FOREIGN KEY (assignment_id) REFERENCES ASSIGNMENT(assignment_id) ON DELETE CASCADE
);

-- Substituted Content Store for TBR
CREATE TABLE MULTIMEDIA_CONTENT (
    content_id          INT NOT NULL,
    submission_id       INT NOT NULL,
    file_type           VARCHAR(50) NOT NULL,
    file_path           VARCHAR(500) NOT NULL,
    extracted_text      LONGTEXT,
    video_title         VARCHAR(255),
    tbr_theme_category  VARCHAR(100),
    CONSTRAINT pk_multimedia_content PRIMARY KEY (content_id),
    CONSTRAINT fk_multimedia_sub FOREIGN KEY (submission_id) REFERENCES SUBMISSION(submission_id) ON DELETE CASCADE
);

-- Facial Vector / CBR Feature Isolation Store
CREATE TABLE FACIAL_EXPRESSION_ANALYSIS (
    analysis_id           INT NOT NULL,
    student_id            VARCHAR(50) NOT NULL,
    eye_position          VARCHAR(100),
    mouth_position        VARCHAR(100),
    facial_landmarks      LONGTEXT, -- Storing JSON string arrays natively inside LONGTEXT
    cbr_expression_result VARCHAR(50),
    CONSTRAINT pk_facial_analysis PRIMARY KEY (analysis_id),
    CONSTRAINT fk_facial_student FOREIGN KEY (student_id) REFERENCES STUDENT(student_id) ON DELETE CASCADE
);


-- =========================================================================
-- 3. DATA INSERTION SECTION
-- =========================================================================

-- --- INSERTING STUDENTS ---
INSERT INTO STUDENT (student_id, student_name, student_email, life_motto, profile_image_path)
VALUES ('B032210001', 'Tan Wei Pin', 'tan@student.utem.edu.my', 'Positive Mindset drives exceptional execution.', '/storage/profiles/b032210001_face.jpg');

INSERT INTO STUDENT (student_id, student_name, student_email, life_motto, profile_image_path)
VALUES ('B032210042', 'Nur Asyiqin binti Abdullah', 'asyiqin@student.utem.edu.my', 'Strive for consistency, not perfection.', '/storage/profiles/b032210042_face.jpg');

INSERT INTO STUDENT (student_id, student_name, student_email, life_motto, profile_image_path)
VALUES ('B032210085', 'Nur Hannah Fatini binti Mohd Azahar', 'hannah@student.utem.edu.my', 'An inspirational life changes perspectives.', '/storage/profiles/b032210085_face.jpg');

INSERT INTO STUDENT (student_id, student_name, student_email, life_motto, profile_image_path)
VALUES ('B032210112', 'Tengku Umairah Khadijah binti Tengku Rithaudden', 'umairah@student.utem.edu.my', 'Motivational core produces resilient structures.', '/storage/profiles/b032210112_face.jpg');


-- --- INSERTING ASSIGNMENTS ---
INSERT INTO ASSIGNMENT (assignment_id, assignment_title, due_date, max_file_size)
VALUES (101, 'Advanced Multimedia Geodatabase Project', STR_TO_DATE('2026-06-01 23:59:59', '%Y-%m-%d %H:%i:%s'), 52428800);

INSERT INTO ASSIGNMENT (assignment_id, assignment_title, due_date, max_file_size)
VALUES (102, 'Real-time Feature Extraction Script', STR_TO_DATE('2026-06-15 18:00:00', '%Y-%m-%d %H:%i:%s'), 20971520);


-- --- INSERTING SUBMISSIONS ---
INSERT INTO SUBMISSION (submission_id, student_id, assignment_id, submission_date, file_size, abr_status)
VALUES (201, 'B032210001', 101, STR_TO_DATE('2026-05-31 14:20:00', '%Y-%m-%d %H:%i:%s'), 31457280, 'On-Time');

INSERT INTO SUBMISSION (submission_id, student_id, assignment_id, submission_date, file_size, abr_status)
VALUES (202, 'B032210042', 101, STR_TO_DATE('2026-06-02 09:15:00', '%Y-%m-%d %H:%i:%s'), 41943040, 'Late Submission');

INSERT INTO SUBMISSION (submission_id, student_id, assignment_id, submission_date, file_size, abr_status)
VALUES (203, 'B032210085', 101, STR_TO_DATE('2026-05-30 21:00:00', '%Y-%m-%d %H:%i:%s'), 62914560, 'Oversized');

INSERT INTO SUBMISSION (submission_id, student_id, assignment_id, submission_date, file_size, abr_status)
VALUES (204, 'B032210112', 102, STR_TO_DATE('2026-06-16 11:00:00', '%Y-%m-%d %H:%i:%s'), 25165824, 'Oversized');


-- --- INSERTING MULTIMEDIA CONTENT ---
INSERT INTO MULTIMEDIA_CONTENT (content_id, submission_id, file_type, file_path, extracted_text, video_title, tbr_theme_category)
VALUES (301, 201, 'PDF', '/storage/docs/b032210001_report.pdf', 
        'This geodatabase report outlines a structured implementation strategy. Sustaining an active, positive mindset across team divisions optimizes development velocity and software resilience.', 
        NULL, 'Positive Mindset');

INSERT INTO MULTIMEDIA_CONTENT (content_id, submission_id, file_type, file_path, extracted_text, video_title, tbr_theme_category)
VALUES (302, 202, 'Video', '/storage/videos/b032210042_demo.mp4', 
        'Technical walk-through mapping architectural points. Project structures aim to leave an inspirational footprint on municipal logistics frameworks.', 
        'Inspirational Architectural Frameworks V1', 'Inspirational');

INSERT INTO MULTIMEDIA_CONTENT (content_id, submission_id, file_type, file_path, extracted_text, video_title, tbr_theme_category)
VALUES (303, 203, 'Video', '/storage/videos/b032210085_presentation.mp4', 
        'Comprehensive asset documentation slide presentation detailing core algorithm execution behaviors.', 
        'Motivational Strategies for Project Lifecycle Management', 'Motivational');

INSERT INTO MULTIMEDIA_CONTENT (content_id, submission_id, file_type, file_path, extracted_text, video_title, tbr_theme_category)
VALUES (304, 204, 'PDF', '/storage/docs/b032210112_script_analysis.pdf', 
        'Standard implementation file mapping operational limits and matrix configurations without major thematic classifications.', 
        NULL, 'Technical-Unclassified');


-- --- INSERTING FACIAL EXPRESSION ANALYSIS ---
INSERT INTO FACIAL_EXPRESSION_ANALYSIS (analysis_id, student_id, eye_position, mouth_position, facial_landmarks, cbr_expression_result)
VALUES (401, 'B032210001', 'X:242,Y:180', 'X:245,Y:290', 
        '{"landmarks": [[242,180], [285,182], [245,290]], "confidence": 0.98, "mesh_version": "v2.1"}', 'Happy');

INSERT INTO FACIAL_EXPRESSION_ANALYSIS (analysis_id, student_id, eye_position, mouth_position, facial_landmarks, cbr_expression_result)
VALUES (402, 'B032210042', 'X:238,Y:182', 'X:240,Y:275', 
        '{"landmarks": [[238,182], [281,183], [240,275]], "confidence": 0.94, "mesh_version": "v2.1"}', 'Neutral');

INSERT INTO FACIAL_EXPRESSION_ANALYSIS (analysis_id, student_id, eye_position, mouth_position, facial_landmarks, cbr_expression_result)
VALUES (403, 'B032210085', 'X:240,Y:195', 'X:242,Y:310', 
        '{"landmarks": [[240,195], [284,196], [242,310]], "confidence": 0.91, "mesh_version": "v2.1"}', 'Surprise');

INSERT INTO FACIAL_EXPRESSION_ANALYSIS (analysis_id, student_id, eye_position, mouth_position, facial_landmarks, cbr_expression_result)
VALUES (404, 'B032210112', 'X:245,Y:178', 'X:244,Y:260', 
        '{"landmarks": [[245,178], [288,180], [244,260]], "confidence": 0.96, "mesh_version": "v2.1"}', 'Sad');
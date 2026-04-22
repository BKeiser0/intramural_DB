
-- sport table
INSERT INTO sport (S_ID, Sport_Type) VALUES
(1, 'Basketball'),
(2, 'Soccer'),
(3, 'Baseball'),
(4, 'Volleyball'),
(5, 'Tennis'),
(6, 'Swimming'),
(7, 'Track and Field'),
(8, 'Football'),
(9, 'Hockey'),
(10, 'Lacrosse');


-- location table
INSERT INTO location (L_ID, Location) VALUES
(1, 'Main Arena'),
(2, 'North Field'),
(3, 'South Gym'),
(4, 'East Court'),
(5, 'West Stadium'),
(6, 'Aquatic Center'),
(7, 'Track Complex'),
(8, 'Downtown Field'),
(9, 'Riverside Court'),
(10, 'Central Park Arena');


-- status table
INSERT INTO status (St_ID, Semester, S_ID, Start_Date, End_Date, Status) VALUES
(1,  'Fall 2024',   1, '2024-09-01', '2024-12-15', 'Completed'),
(2,  'Fall 2024',   2, '2024-09-01', '2024-12-15', 'Completed'),
(3,  'Spring 2025', 3, '2025-01-15', '2025-05-10', 'Completed'),
(4,  'Spring 2025', 4, '2025-01-15', '2025-05-10', 'Completed'),
(5,  'Fall 2025',   5, '2025-09-01', '2025-12-15', 'Active'),
(6,  'Fall 2025',   6, '2025-09-01', '2025-12-15', 'Active'),
(7,  'Fall 2025',   7, '2025-09-01', '2025-12-15', 'Active'),
(8,  'Spring 2026', 8, '2026-01-15', NULL,          'Upcoming'),
(9,  'Spring 2026', 9, '2026-01-15', NULL,          'Upcoming'),
(10, 'Spring 2026', 10,'2026-01-15', NULL,          'Upcoming');


-- people table
INSERT INTO people (P_ID, Name) VALUES
(1,  'James Carter'),
(2,  'Maria Lopez'),
(3,  'Kevin Smith'),
(4,  'Aisha Brown'),
(5,  'Tyler Johnson'),
(6,  'Sara Williams'),
(7,  'Chris Davis'),
(8,  'Nina Wilson'),
(9,  'Omar Hassan'),
(10, 'Lily Chen'),
(11, 'John Smith'),
(12, 'Dayton James'),
(13, 'Priya Patel'),
(14, 'Marcus Reed'),
(15, 'Chloe Martinez'),
(16, 'Ethan Brooks'),
(17, 'Fatima Nour'),
(18, 'Derek Stone'),
(19, 'Yuna Kim'),
(20, 'Brandon Lee'),
(21, 'Alexis Turner'),
(22, 'Samuel Grant'),
(23, 'Zoe Adams'),
(24, 'Isaiah Bell'),
(25, 'Hana Takahashi'),
(26, 'Caleb Rivera'),
(27, 'Amara Osei'),
(28, 'Liam Murphy'),
(29, 'Sofia Reyes'),
(30, 'Nathan Clarke'),
(31, 'Destiny Walker'),
(32, 'Jordan Hughes'),
(33, 'Elena Vasquez'),
(34, 'Malik Thompson'),
(35, 'Ingrid Sorensen'),
(36, 'DeShawn Mitchell'),
(37, 'Camille Dubois'),
(38, 'Ryan O''Brien'),
(39, 'Keiko Yamamoto'),
(40, 'Terrence Ford'),
(41, 'Layla Saleh'),
(42, 'Austin Perry'),
(43, 'Bianca Ferreira'),
(44, 'Darius Cole'),
(45, 'Mei-Ling Wu'),
(46, 'Patrick Nguyen'),
(47, 'Serena Blake'),
(48, 'Kofi Mensah'),
(49, 'Adriana Morales'),
(50, 'Trevor Simmons');


-- team table
INSERT INTO team (T_ID, S_ID, C_ID) VALUES
(1,  1,  1),
(2,  2,  6),
(3,  3,  11),
(4,  4,  16),
(5,  5,  21),
(6,  6,  26),
(7,  7,  31),
(8,  8,  36),
(9,  9,  41),
(10, 10, 46);


-- team roster table
INSERT INTO team_roster (T_ID, P_ID) VALUES
-- Team 1 (Basketball)
(1,  1),
(1,  2),
(1,  3),
(1,  4),
(1,  5),
-- Team 2 (Soccer)
(2,  6),
(2,  7),
(2,  8),
(2,  9),
(2,  10),
-- Team 3 (Baseball)
(3,  11),
(3,  12),
(3,  13),
(3,  14),
(3,  15),
-- Team 4 (Volleyball)
(4,  16),
(4,  17),
(4,  18),
(4,  19),
(4,  20),
-- Team 5 (Tennis)
(5,  21),
(5,  22),
(5,  23),
(5,  24),
(5,  25),
-- Team 6 (Swimming)
(6,  26),
(6,  27),
(6,  28),
(6,  29),
(6,  30),
-- Team 7 (Track and Field)
(7,  31),
(7,  32),
(7,  33),
(7,  34),
(7,  35),
-- Team 8 (Football)
(8,  36),
(8,  37),
(8,  38),
(8,  39),
(8,  40),
-- Team 9 (Hockey)
(9,  41),
(9,  42),
(9,  43),
(9,  44),
(9,  45),
-- Team 10 (Lacrosse)
(10, 46),
(10, 47),
(10, 48),
(10, 49),
(10, 50);


-- scheduling table
INSERT INTO scheduling (G_ID, S_ID, Location, T1_ID, T2_ID) VALUES
(1,  1, 1,  1,  2),
(2,  1, 2,  1,  2),
(3,  2, 3,  3,  4),
(4,  2, 4,  3,  4),
(5,  3, 5,  5,  6),
(6,  3, 6,  5,  6),
(7,  4, 7,  7,  8),
(8,  4, 8,  7,  8),
(9,  5, 9,  9,  10),
(10, 5, 10, 9,  10);


-- score table
INSERT INTO score (G_ID, P_ID, T_ID, Points) VALUES
-- Game 1: Team 1 vs Team 2
(1,  1,  1,  22),
(1,  2,  1,  15),
(1,  3,  1,  10),
(1,  4,  1,  8),
(1,  5,  1,  5),
(1,  6,  2,  18),
(1,  7,  2,  12),
(1,  8,  2,  9),
(1,  9,  2,  7),
(1,  10, 2,  4),
-- Game 2: Team 1 vs Team 2
(2,  1,  1,  30),
(2,  2,  1,  20),
(2,  3,  1,  14),
(2,  4,  1,  11),
(2,  5,  1,  6),
(2,  6,  2,  25),
(2,  7,  2,  17),
(2,  8,  2,  13),
(2,  9,  2,  8),
(2,  10, 2,  5),
-- Game 3: Team 3 vs Team 4
(3,  11, 3,  3),
(3,  12, 3,  2),
(3,  13, 3,  1),
(3,  14, 3,  2),
(3,  15, 3,  1),
(3,  16, 4,  1),
(3,  17, 4,  0),
(3,  18, 4,  1),
(3,  19, 4,  2),
(3,  20, 4,  0),
-- Game 4: Team 3 vs Team 4
(4,  11, 3,  2),
(4,  12, 3,  1),
(4,  13, 3,  3),
(4,  14, 3,  1),
(4,  15, 3,  0),
(4,  16, 4,  2),
(4,  17, 4,  1),
(4,  18, 4,  0),
(4,  19, 4,  3),
(4,  20, 4,  1),
-- Game 5: Team 5 vs Team 6
(5,  21, 5,  7),
(5,  22, 5,  5),
(5,  23, 5,  4),
(5,  24, 5,  6),
(5,  25, 5,  3),
(5,  26, 6,  4),
(5,  27, 6,  3),
(5,  28, 6,  5),
(5,  29, 6,  2),
(5,  30, 6,  4),
-- Game 6: Team 5 vs Team 6
(6,  21, 5,  10),
(6,  22, 5,  8),
(6,  23, 5,  6),
(6,  24, 5,  7),
(6,  25, 5,  5),
(6,  26, 6,  8),
(6,  27, 6,  6),
(6,  28, 6,  7),
(6,  29, 6,  4),
(6,  30, 6,  5),
-- Game 7: Team 7 vs Team 8
(7,  31, 7,  14),
(7,  32, 7,  11),
(7,  33, 7,  9),
(7,  34, 7,  12),
(7,  35, 7,  8),
(7,  36, 8,  10),
(7,  37, 8,  13),
(7,  38, 8,  7),
(7,  39, 8,  9),
(7,  40, 8,  6),
-- Game 8: Team 7 vs Team 8
(8,  31, 7,  16),
(8,  32, 7,  10),
(8,  33, 7,  12),
(8,  34, 7,  9),
(8,  35, 7,  7),
(8,  36, 8,  18),
(8,  37, 8,  15),
(8,  38, 8,  11),
(8,  39, 8,  8),
(8,  40, 8,  6),
-- Game 9: Team 9 vs Team 10
(9,  41, 9,  5),
(9,  42, 9,  3),
(9,  43, 9,  4),
(9,  44, 9,  2),
(9,  45, 9,  3),
(9,  46, 10, 4),
(9,  47, 10, 3),
(9,  48, 10, 5),
(9,  49, 10, 2),
(9,  50, 10, 1),
-- Game 10: Team 9 vs Team 10
(10, 41, 9,  6),
(10, 42, 9,  4),
(10, 43, 9,  5),
(10, 44, 9,  3),
(10, 45, 9,  2),
(10, 46, 10, 7),
(10, 47, 10, 5),
(10, 48, 10, 4),
(10, 49, 10, 3),
(10, 50, 10, 2);


-- history table
INSERT INTO history (G_ID, T1_ID, T2_ID, GameLength, Winner) VALUES
(1,  1,  2,  48,  1),
(2,  1,  2,  50,  2),
(3,  3,  4,  90,  3),
(4,  3,  4,  90,  4),
(5,  5,  6,  120, 5),
(6,  5,  6,  110, 6),
(7,  7,  8,  60,  7),
(8,  7,  8,  65,  8),
(9,  9,  10, 75,  9),
(10, 9,  10, 80,  10);

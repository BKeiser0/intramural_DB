
-- 1)  For each game, which player scored the most points and what team were they on?

SELECT s.G_ID, p.Name, s.T_ID, s.Points
FROM score s
JOIN people p ON s.P_ID = p.P_ID
WHERE s.Points = (
    SELECT MAX(s2.Points)
    FROM score s2
    WHERE s2.G_ID = s.G_ID ) 
ORDER BY s.G_ID;

-- 2) Which people in the database are not on any team? 

SELECT p.P_ID, p.Name
FROM people p
LEFT JOIN team_roster tr ON p.P_ID = tr.P_ID
WHERE tr.T_ID IS NULL;

-- 3)  Which sport has the most games scheduled, ranked from most to least?

SELECT s.Sport_Type, COUNT(G_ID)
FROM sport s
JOIN scheduling sc on s.S_ID = sc.S_ID
GROUP BY s.Sport_Type
ORDER BY COUNT(sc.G_ID) DESC

-- 4)  Which game had the highest combined total points across all players and at what location was it played?

 SELECT Location
 FROM location
 WHERE L_ID = (
     SELECT location
     FROM scheduling
     WHERE G_ID = (
        SELECT G_ID
        FROM score
        GROUP BY G_ID
        ORDER BY SUM(Points) DESC
        limit 1));


-- 5) Ranking every player by their cumulative points scored across all games they appeared in.

SELECT p.Name, tr.T_ID, SUM(s.Points) AS Total_Points
FROM people p
JOIN team_roster tr ON p.P_ID = tr.P_ID
JOIN score s ON p.P_ID = s.P_ID
GROUP BY p.P_ID, p.Name, tr.T_ID
ORDER BY Total_Points DESC;

-- 6) how many players are on each team?

SELECT T_ID, count(P_ID) as player_count
FROM team_roster
GROUP BY T_ID


-- 7) average points per game?

SELECT G_ID, AVG(Points) AS Avg_Points
FROM score
GROUP BY G_ID;


-- 8) list all the teams and their captains

SELECT t.T_ID, p.Name AS Captain
FROM team t
JOIN people p ON t.C_ID = p.P_ID;

-- 9) How many games has each player appeared in?

SELECT p.Name, COUNT(DISTINCT s.G_ID) AS Games_Played
FROM people p
JOIN score s ON p.P_ID = s.P_ID
GROUP BY p.P_ID, p.Name
ORDER BY Games_Played DESC;

-- 10) List all players who scored above the overall average points in any game

SELECT DISTINCT p.Name, s.Points, s.G_ID
FROM people p
JOIN score s ON p.P_ID = s.P_ID
WHERE s.Points > (
    SELECT AVG(Points)
    FROM score
)
ORDER BY s.Points DESC;

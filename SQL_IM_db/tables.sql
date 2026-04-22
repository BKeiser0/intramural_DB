
-- generate people table
CREATE TABLE people(
    P_ID integer PRIMARY KEY,
    Name TEXT NOT NULL
   
);


-- generate team table
CREATE TABLE team(
    T_ID integer PRIMARY KEY,
    S_ID integer NOT NULL,
    C_ID integer
   
);

-- create team roster table
CREATE TABLE team_roster(
    T_ID integer NOT NULL,
    P_ID integer NOT NULL,
    PRIMARY KEY (T_ID, P_ID),
    FOREIGN KEY (T_ID) REFERENCES team(T_ID),
    FOREIGN KEY (P_ID) REFERENCES people(P_ID)
);



-- generate location table
CREATE TABLE location(
    L_ID integer PRIMARY KEY,
    Location TEXT NOT NULL
);


-- generate sport table
CREATE TABLE sport(
    S_ID integer PRIMARY KEY,
    Sport_Type TEXT NOT NULL
);


-- generate status table
CREATE TABLE status(
    St_ID integer PRIMARY KEY,
    Semester TEXT NOT NULL,
    S_ID integer not null,
    Start_Date date not null,
    End_Date date,
    Status TEXT NOT NULL
);

-- generate scheduling table
CREATE TABLE scheduling(
    G_ID integer PRIMARY KEY,
    S_ID integer not null,
    Location integer NOT NULL,
    T1_ID integer not null,
    T2_ID integer not null
);


-- generate score table
CREATE TABLE score(
    G_ID integer not null,
    P_ID integer not null,
    T_ID integer not null,
    Points integer NOT NULL,      
    PRIMARY KEY (G_ID,P_ID)
);


-- generate history table
CREATE TABLE history(
    G_ID integer PRIMARY KEY,
    T1_ID integer not null,
    T2_ID integer not null,
    GameLength integer NOT NULL,
    Winner integer not null
);


-- adding the foreign key constraints

-- Team FK constraints
ALTER TABLE team
    ADD CONSTRAINT fk_team_sport2
    FOREIGN KEY (S_ID) REFERENCES sport(S_ID);


ALTER TABLE team
    ADD CONSTRAINT fk_team_people2
    FOREIGN KEY (C_ID) REFERENCES people(P_ID);


-- Status FK constraints
ALTER TABLE status
    ADD CONSTRAINT fk_status_sport
    FOREIGN KEY (S_ID) REFERENCES sport(S_ID);




-- Scheduling FK constraints
ALTER TABLE scheduling
    ADD CONSTRAINT fk_scheduling_sport2
    FOREIGN KEY (S_ID) REFERENCES sport(S_ID);

ALTER TABLE scheduling
    ADD CONSTRAINT fk_scheduling_location
    FOREIGN KEY (Location) REFERENCES location(L_ID);

ALTER TABLE scheduling
    ADD CONSTRAINT fk_scheduling_team1
    FOREIGN KEY (T1_ID) REFERENCES team(T_ID);

ALTER TABLE scheduling
    ADD CONSTRAINT fk_scheduling_team2
    FOREIGN KEY (T2_ID) REFERENCES team(T_ID);



-- Score FK constraints
ALTER TABLE score
    ADD CONSTRAINT fk_score_game
    FOREIGN KEY (G_ID) REFERENCES scheduling(G_ID);

ALTER TABLE score
    ADD CONSTRAINT fk_score_player
    FOREIGN KEY (P_ID) REFERENCES people(P_ID);


ALTER TABLE score
    ADD CONSTRAINT fk_score_team
    FOREIGN KEY (T_ID) REFERENCES team(T_ID);




-- History FK constraints
ALTER TABLE history
    ADD CONSTRAINT fk_history_game
    FOREIGN KEY (G_ID) REFERENCES scheduling(G_ID);

ALTER TABLE history
    ADD CONSTRAINT fk_history_team1
    FOREIGN KEY (T1_ID) REFERENCES team(T_ID);


ALTER TABLE history
    ADD CONSTRAINT fk_history_team2
    FOREIGN KEY (T2_ID) REFERENCES team(T_ID);


ALTER TABLE history
    ADD CONSTRAINT fk_history_winner
    FOREIGN KEY (Winner) REFERENCES team(T_ID);

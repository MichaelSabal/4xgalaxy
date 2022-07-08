--CREATE DATABASE alliancegame;

--GRANT ALL ON alliancegame.* TO '4xgalaxy'@'localhost' IDENTIFIED BY '#A4%s6&d8';

USE msabalne_alliancegame;

CREATE TABLE GalaxySettings (
	settingID char(40) not null primary key
	,settingValue varchar(128) not null
);
INSERT INTO GalaxySettings (settingID,settingValue) VALUES
('Version','0.03a'),
('GalaxyWidth','100'),
('GalaxyHeight','100'),
('MaxStars','250'),
('GameCalendar','2000.00'),
('LastCRON',current_time());

CREATE TABLE EventTypes (
	eventType int not null primary key
	,eventTypeDescription varchar(256) not null
);
INSERT INTO EventTypes (eventType,eventTypeDescription) VALUES
(1,'Connection event'),
(2,'Cron event'),
(3,'Message sent'),
(4,'Player action'),
(5,'Game environment changed'),
(6,'MySQL anomoly'),
(7,'PHP anomoly'),
(8,'Network anomoly'),
(9,'Content request');

CREATE TABLE EventLog (
	eventID int not null auto_increment primary key
	,eventType int not null
	,eventTime datetime not null
	,playerID int
	,eventDescription varchar(4096) not null
);

-- A heliosphere has a typical diameter of 2 light-years.
-- 1 light-year = 63241.0770881 AU.
-- Radii will be defined in terms of AU.
-- Location coordinates will be defined in terms of light years.
CREATE TABLE Stars (
	starID int not null auto_increment primary key
	,starRandomName varchar(128) not null
	,playerID int
	,starAssignedName varchar(128)
	,locationX decimal(11,3) not null
	,locationY decimal(11,3) not null
	,radius decimal(10,8) not null
	,starColor char(2) not null
);

-- Orbit is in terms of AU distance from the surface of the parent
-- Period is the number of game years it takes to complete one revolution
-- Radius is in km: 1 AU = 149597870.691km
CREATE TABLE HeliosphereObjectTypes (
	heliosphereObjectType int not null primary key
	,heliosphereObjectTypeDescription varchar(128) not null
	,parentObjectType int
	,minRadius decimal(19,8) not null
	,maxRadius decimal(19,8) not null
	,minOrbit decimal(19,8) not null
	,maxOrbit decimal(19,8) not null
	,minPeriod decimal(11,4) not null
	,maxPeriod decimal(11,4) not null
);
INSERT INTO HeliosphereObjectTypes VALUES
(1,'Galactic Core',null,0,0,0,0,0,0),
(2,'Star',1,747989.353455,747989353.455,0,0,0,0),
(3,'Planet',2,100.0,250000,0.03,33000,0.01,300),
(4,'Moon',3,100,6000,0.0004,0.0126,0.01,1);

-- Ships should not be considered heliosphere objects since they travel between stars
-- Temperature is in degrees Kelvin
-- Theta equals how far into the revolution the object is, in degrees
CREATE TABLE HeliosphereObjects (
	heliosphereObject int not null auto_increment primary key
	,heliosphereObjectType int not null
	,heliosphereObjectRandomName varchar(128) not null
	,heliosphereObjectAssignedName varchar(128)
	,parentObject int
	,starID int
	,habitable char(1) not null default 'N'
	,population int not null default 0
	,apogee decimal(19,8) not null
	,perigee decimal(19,8) not null
	,radius decimal(19,8) not null
	,period decimal(11,4) not null
	,theta numeric(6,2) not null default 0.00
	,playerID int
	,temperature int
	,surfaceType char(3) not null default 'GAS'
	,imageFile varchar(1024)
);
INSERT INTO HeliosphereObjects (heliosphereObject,heliosphereObjectType,heliosphereObjectRandomName,apogee,perigee,radius,period) VALUES
	(1,1,'Galactic Core',0,0,0,0);

-- Note: If running a MySQL database older than 5.5.3, secret should be changed to varbinary(128)
-- PlayerType: H=Human, C=Computer, A=Admin, M=Moderator
CREATE TABLE Players (
	playerID int not null auto_increment primary key
	,playerType char(1) not null
	,birthday datetime
	,email varchar(256)
	,alias varchar(80) not null
	,ipaddress4 varchar(15) not null
	,ipaddress6 varchar(48) not null
	,firstName varchar(80)
	,lastName varchar(80)
	,firstStar int not null
	,dateJoined datetime not null
	,lastLogin datetime not null
	,secret varchar(128)
);

CREATE TABLE SurfaceTypes (
	surfaceType char(3) not null primary key
	,surfaceTypeName varchar(20) not null
	,surfaceTypeDescription varchar(80) not null
	,habitable char(1) not null default 'N'
);
INSERT INTO SurfaceTypes VALUES
('GAS','Gas','Stars or gas giant planets','N'),
('ICE','Ice','Comets, icy moons, and dwarf planets','Y'),
('ROK','Rock','Most habitable planets, moons, and asteroids','Y'),
('CRY','Crystal','Various types of crystal other than ice','N'),
('WAT','Water','Water worlds - rocky layer covered by > 95% water','Y'),
('LAV','Lava','Molten rock or metal','N');

-- version 0.02e
/*
UPDATE GalaxySettings SET settingValue='0.02e' WHERE settingID='Version';
ALTER TABLE SurfaceTypes
ADD COLUMN SurfaceTypeName varchar(20) not null;
INSERT INTO GalaxySettings (settingID,settingValue) VALUES
('LastCRON',current_time());
UPDATE SurfaceTypes SET SurfaceTypeName='Gas' WHERE SurfaceType='GAS';
UPDATE SurfaceTypes SET SurfaceTypeName='Ice' WHERE SurfaceType='ICE';
UPDATE SurfaceTypes SET SurfaceTypeName='Rock' WHERE SurfaceType='ROK';
UPDATE SurfaceTypes SET SurfaceTypeName='Crystal' WHERE SurfaceType='CRY';
UPDATE SurfaceTypes SET SurfaceTypeName='Water' WHERE SurfaceType='WAT';
UPDATE SurfaceTypes SET SurfaceTypeName='Lava' WHERE SurfaceType='LAV';


--version 0.03a
UPDATE GalaxySettings SET settingValue='0.03a' WHERE settingID='Version';
INSERT INTO EventTypes (eventType,eventTypeDescription) VALUES
(9,'Content request');
*/
CREATE TABLE Nations (
	nationID int auto_increment not null primary key
	,playerID int not null
	,heliosphereObjectID int not null
	,nationName varchar(256)
	,population numeric(12,0)
);

CREATE TABLE TechnologyTypes (
	technologyTypeID int not null primary key
	,technologyTypeDescription varchar(128) not null
);
INSERT INTO TechnologyTypes VALUES
(1,'Food production'),
(2,'Food storage'),
(3,'Mining'),
(4,'Architecture'),
(5,'Shielding'),
(6,'Weapons'),
(7,'Ships'),
(8,'Communications'),
(9,'Optics'),
(10,'Education'),
(11,'Automation');

CREATE TABLE ResourceTypeUsages (
	resourceTypeUsageID char(5) not null primary key
	,resourceTypeUsageDescription varchar(128) not null
);
-- Note on RAW: Everything can be used as a raw material for something else.
-- So the RAW type usage only applies when no other usage makes sense.
INSERT INTO ResourceTypeUsages VALUES
('RAW','No use besides as a raw material'),
('HELTH','General health'),
('CONST','Construction'),
('ENTER','Entertainment'),
('TECHN','Technology'),
('EDUCA','Education'),
('TRANS','Transportation'),
('DECOR','Culture'),
('MEDIC','Emergency health');

CREATE TABLE ResourceTypes (
	resourceTypeID int not null auto_increment primary key
	,resourceTypeDescription varchar(128) not null
	,primaryUsage char(5) not null
	,secondUsage char(5)
	,thirdUsage char(5)
	,fourthUsage char(5)
);
INSERT INTO ResourceTypes (resourceTypeDescription,primaryUsage,secondUsage) VALUES
('Food','HELTH',null),
('Livestock','HELTH','ENTER'),
('Beverage','HELTH',null),
('Alcohol','ENTER','MEDIC'),
('Element','RAW',null),
('Medicine','MEDIC',null),
('Machine','CONST',null),
('Optics','TECHN','EDUCA'),
('Clothing','HELTH','DECOR'),
('Musical Instrument','DECOR',null),
('Energy','TECHN',null),
('Alloy','RAW',null);

-- ResourceSource may be 'P' for player created or 'S' for system created.
-- Players may create their own resources and develop a backstory to convince other
-- 	players to purchase their creations.
-- ResourceCreation may be 'N' for natural (consumes no other resources), or 'M' for manufactured (consumes other resources)
-- TechnologyRequired is a composite field of TechnologyIDs that must be available on the planet for this resource to be gathered/made
-- Population is how many people can be sustained with a single unit of this resource
-- RenewalRate is how many units of a resource are added per year
-- DecayRate is how many years before a resource returns to its raw materials (if manufactured)
-- 	or how many years before a resource becomes unusable (if natural).
CREATE TABLE Resources (
	resourceID int not null auto_increment primary key
	,approved char(1) not null default 'N'
	,renewable char(1) not null default 'N'
	,resourceName varchar(128) not null
	,resourceDescription varchar(4096)
	,resourceTypeID int not null
	,resourcePrimaryUsage char(5)
	,resourceSource char(1) not null
	,playerID int
	,resourceCreation char(1) not null
	,population numeric(11,2) not null default 0
	,basicTimeToProduce numeric(11,2) not null
	,technologyRequired varchar(128)
	,basicRenewalRate numeric(19,4)
	,basicDecayRate numeric(11,4)
);
INSERT INTO Resources (approved,renewable,resourceName,resourceDescription,resourceTypeID,resourcePrimaryUsage,resourceSource,playerID,
	resourceCreation,population,basicTimeToProduce,technologyRequired,basicRenewalRate,basicDecayRate) VALUES
('Y','Y','Grain','',(SELECT ResourceTypeID FROM ResourceTypes WHERE resourceTypeDescription='Food'),'HELTH','S',-1,'N',1,0.5,null,1,1),
('Y','Y','Water','',(SELECT ResourceTypeID FROM ResourceTypes WHERE resourceTypeDescription='Beverage'),'HELTH','S',-1,'N',10,0,null,0.1,0),
('Y','N','Ale','',(SELECT ResourceTypeID FROM ResourceTypes WHERE resourceTypeDescription='Alcohol'),'ENTER','S',-1,'M',4,0.33,null,0,30);

CREATE TABLE ResourceConsumption (
	resourceID int not null
	,usesResourceID int not null
	,amountPerOne numeric(11,3) not null
);
INSERT INTO ResourceConsumption VALUES
((SELECT resourceID FROM Resources WHERE resourceName='Ale'),(SELECT resourceID FROM Resources WHERE resourceName='Grain'),1),
((SELECT resourceID FROM Resources WHERE resourceName='Ale'),(SELECT resourceID FROM Resources WHERE resourceName='Water'),2);

CREATE TABLE Technologies (
	technologyID int not null auto_increment primary key
	,technologyTypeID int not null
	,technologyName varchar(128) not null
	,technologyLevel int not null
	,technologyDescription varchar(4096)
	,distanceLY numeric(19,8)
	,percentFaster numeric(11,3)
	,productionIncreasePercent numeric(11,3)
	,costReductionPercent numeric(6,2)
	,affectsResourceType char(5) not null
	,prerequisites varchar(1024)
);
CREATE TABLE TechnologyResourceRequirements (
	technologyID int not null
	,resourceID int not null
	,amountRequired numeric(11,3) not null
);

CREATE TABLE NationTechnology (
	nationTechID int not null auto_increment primary key
	,technologyID int not null
	,nationID int not null
	,percentComplete numeric(6,2) not null default 0
);

CREATE TABLE NationResources (
	nationResourceID int not null auto_increment primary key
	,nationID int not null
	,resourceID int not null
	,amountOnHand numeric(40,4) not null
);

/*
CREATE PROCEDURE Reset()
AS BEGIN
truncate eventlog;
truncate players;
truncate stars;
truncate HeliosphereObjects;
INSERT INTO HeliosphereObjects (heliosphereObject,heliosphereObjectType,heliosphereObjectRandomName,apogee,perigee,radius,period) VALUES
	(1,1,'Galactic Core',0,0,0,0);
truncate nations;
truncate nationtechnology;
truncate nationresources;
END
*/

/*


CREATE TABLE ShipTypes

CREATE TABLE Ships


CREATE TABLE PersonalMessages

CREATE TABLE GrandExchange

CREATE TABLE Priests

CREATE TABLE Alliances

CREATE TABLE Treaties

*/

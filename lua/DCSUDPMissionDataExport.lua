-- Copyright 2016 Marcel Haupt
-- http://marcel-haupt.eu/
--
-- Licensed under the Apache License, Version 2.0 (the "License");
-- you may not use this file except in compliance with the License.
-- You may obtain a copy of the License at
--
-- http ://www.apache.org/licenses/LICENSE-2.0
--
-- Unless required by applicable law or agreed to in writing, software
-- distributed under the License is distributed on an "AS IS" BASIS,
-- WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
-- See the License for the specific language governing permissions and
-- limitations under the License.

-- THis is a Modification of the original code from xcom at http://forums.eagle.ru/showthread.php?t=124715&highlight=create+statistics
--
-- Github Project: https://github.com/cbacon93/DCSServerStats


if CbaconExp==nil then		-- Protection against multiple references (typically wrong script installation)


local UDPip = "127.0.0.1"
local UDPport = "9182"



package.path  = package.path..";.\\LuaSocket\\?.lua" .. ";.\\Scripts\\?.lua"
package.cpath = package.cpath..";.\\LuaSocket\\?.dll"
local socket = require("socket")


--Neccessary tables for string instead of intagers
local SETCoalition = 
{
	[1] = "red",
	[2] = "blue",
}

local SETGroupCat = 
{
	[1] = "AIRPLANE",
	[2] = "HELICOPTER",
	[3] = "GROUND",
	[4] = "SHIP",
}

local SETfield =
{ 
	[1] = "Time",
	[2] = "Event",
	[3] = "Initiator ID",
	[4] = "Initiator Coalition",
	[5] = "Initiator Group Category",
	[6] = "Initiator Type",
	[7] = "Initiator Player",
	[8]	= "Weapon Category",
	[9]	= "Weapon Name",
	[10] = "Target ID",
	[11] = "Target Coalition",
	[12] = "Target Group Category",
	[13] = "Target Type",
	[14] = "Target Player",
}

local SETWeaponCatName = 
{
	[0] = "SHELL",
	[1] = "MISSILE",
	[2] = "ROCKET",
	[3] = "BOMB",
 }
 
 local wEvent = {
	[0] = "S_EVENT_INVALID",
	[1] = "S_EVENT_SHOT",
	[2] = "S_EVENT_HIT",
	[3] = "S_EVENT_TAKEOFF",
	[4] = "S_EVENT_LAND",
	[5] = "S_EVENT_CRASH",
	[6] = "S_EVENT_EJECTION",
	[7] = "S_EVENT_REFUELING",
	[8] = "S_EVENT_DEAD",
	[9] = "S_EVENT_PILOT_DEAD",
	[10] = "S_EVENT_BASE_CAPTURED",
	[11] = "S_EVENT_MISSION_START",
	[12] = "S_EVENT_MISSION_END",
	[13] = "S_EVENT_TOOK_CONTROL",
	[14] = "S_EVENT_REFUELING_STOP",
	[15] = "S_EVENT_BIRTH",
	[16] = "S_EVENT_HUMAN_FAILURE",
	[17] = "S_EVENT_ENGINE_STARTUP",
	[18] = "S_EVENT_ENGINE_SHUTDOWN",
	[19] = "S_EVENT_PLAYER_ENTER_UNIT",
	[20] = "S_EVENT_PLAYER_LEAVE_UNIT",
	[21] = "S_EVENT_PLAYER_COMMENT",
	[22] = "S_EVENT_SHOOTING_START",
	[23] = "S_EVENT_SHOOTING_END",
	[24] = "S_EVENT_MAX",
 }


CbaconExp={}

function CbaconExp:onEvent(e)
	local InitID_ = ""
	local WorldEvent = wEvent[e.id]
	local InitCoa = ""
	local InitGroupCat = ""
	local InitType = ""
	local InitPlayer = ""
	local eWeaponCat = ""
	local eWeaponName = ""
	local TargID_ = ""
	local TargType = ""
	local TargPlayer = ""
	local TargCoa = ""
	local TargGroupCat = ""
	
	-- safe world event
	if WorldEvent == nil then
		WorldEvent = "S_EVENT_UNKNOWN"
	end
	
	--Initiator variables
	if e.initiator then
		if string.sub(e.initiator:getName(),1,string.len("CARGO"))~="CARGO" then
			
			--safety - hit building or unmanned vehicle
			if not e.initiator['getPlayerName'] then
				return
			end
			
			--Get initiator player name or AI if NIL
			if not e.initiator:getPlayerName() then
				InitPlayer = "AI"
			else
				InitPlayer = e.initiator:getPlayerName()
			end
		
			--Check Category of object
			--If no category
			if not Object.getCategory(e.initiator) then
				InitID_ = e.initiator.id_
				InitCoa = SETCoalition[e.initiator:getCoalition()]
				InitGroupCat = SETGroupCat[e.initiator:getCategory()]
				InitType = e.initiator:getTypeName()
			--if Category is UNIT	
			elseif Object.getCategory(e.initiator) == Object.Category.UNIT then
				local InitGroup = e.initiator:getGroup()
				InitID_ = e.initiator.id_
				InitCoa = SETCoalition[InitGroup:getCoalition()]
				InitGroupCat = SETGroupCat[InitGroup:getCategory() + 1]
				InitType = e.initiator:getTypeName()
			--if Category is STATIC
			elseif  Object.getCategory(e.initiator) == Object.Category.STATIC then
				InitID_ = e.initiator.id_
				InitCoa = SETCoalition[e.initiator:getCoalition()]
				InitGroupCat = SETGroupCat[e.initiator:getCategory()]
				InitType = e.initiator:getTypeName()
			end
		elseif not e.initiator then
			InitID_ = "No Initiator"
			InitCoa = "No Initiator"
			InitGroupCat = "No Initiator"
			InitType = "No Initiator"
			InitPlayer = "No Initiator"
		end
	end

	--Weapon variables	
	if e.weapon == nil then
		eWeaponCat = "No Weapon"
		eWeaponName = "No Weapon"
	else
		local eWeaponDesc = e.weapon:getDesc()
		eWeaponCat = SETWeaponCatName[eWeaponDesc.category]
		eWeaponName = eWeaponDesc.displayName
	end
	
	--Target variables
	if e.target then
		if string.sub(e.target:getName(),1,string.len("CARGO"))~="CARGO" then
			
			--safety - hit building or unmanned vehicle
			if not e.target['getPlayerName'] then
				return
			end
			
			--Get target player name or AI if NIL
			if not e.target:getPlayerName() then
				TargPlayer = "AI"
			else
				TargPlayer = e.target:getPlayerName()
			end
		
			--Check Category of object
			--If no category
			if not Object.getCategory(e.target) then
				TargID_ = e.target.id_
				TargCoa = SETCoalition[e.target:getCoalition()]
				TargGroupCat = SETGroupCat[e.target:getCategory()]
				TargType = e.target:getTypeName()
			--if Category is UNIT	
			elseif Object.getCategory(e.target) == Object.Category.UNIT then
				local TargGroup = e.target:getGroup()
				TargID_ = e.target.id_
				TargCoa = SETCoalition[TargGroup:getCoalition()]
				TargGroupCat = SETGroupCat[TargGroup:getCategory() + 1]
				TargType = e.target:getTypeName()
			--if Category is STATIC
			elseif  Object.getCategory(e.target) == Object.Category.STATIC then
				TargID_ = e.target.id_
				TargCoa = SETCoalition[e.target:getCoalition()]
				TargGroupCat = SETGroupCat[e.target:getCategory()]
				TargType = e.target:getTypeName()
			end
		elseif not e.target then
			TargID_ = "No target"
			TargCoa = "No target"
			TargGroupCat = "No target"
			TargType = "No target"
			TargPlayer = "No target"
		end
	end
	
	
	
	--write events to table
	if e.id == world.event.S_EVENT_HIT 
	or e.id == world.event.S_EVENT_SHOT
	or e.id == world.event.S_EVENT_EJECTION
	or e.id == world.event.S_EVENT_BIRTH
	or e.id == world.event.S_EVENT_CRASH
	or e.id == world.event.S_EVENT_DEAD
	or e.id == world.event.S_EVENT_PILOT_DEAD
	or e.id == world.event.S_EVENT_LAND
	or e.id == world.event.S_EVENT_MISSION_START
	or e.id == world.event.S_EVENT_MISSION_END
	or e.id == world.event.S_EVENT_TAKEOFF then
	
		udp = socket.udp()
		udp:settimeout(0)
		udp:setpeername(UDPip, UDPport)
		
		local sendstr = math.floor(timer.getTime()) .. "," .. WorldEvent .. "," .. InitID_ .. "," .. InitCoa .. "," .. InitGroupCat .. "," .. InitType .. "," .. InitPlayer .. "," .. eWeaponCat .. "," .. eWeaponName .. "," .. TargID_ .. "," .. TargCoa .. "," .. TargGroupCat .. "," .. TargType .. "," .. TargPlayer
		--env.info(sendstr, true)
		
		udp:send(sendstr)
	end
end






world.addEventHandler(CbaconExp)

end							-- Protection against multiple references (typically wrong script installation)

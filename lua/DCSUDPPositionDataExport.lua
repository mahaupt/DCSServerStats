-- Copyright 2016 Marcel Haupt
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

if CbaconPosExp==nil then		-- Protection against multiple references (typically wrong script installation)


local UDPip = "192.168.178.29"
local UDPport = "9182"


package.path  = package.path..";.\\LuaSocket\\?.lua" .. ";.\\Scripts\\?.lua"
package.cpath = package.cpath..";.\\LuaSocket\\?.dll"
local socket = require("socket")


local SETCoalition = 
{
	[1] = "red",
	[2] = "blue",
}


CbaconPosExpClass = {
	DefaultUpdatePeriod=30,
	LastUpdate=0,
	
	Frame=function(self)
		
		local CurrentTime=math.floor(LoGetModelTime())
		
		if (self.LastUpdate + self.DefaultUpdatePeriod < CurrentTime) then
			self.LastUpdate = CurrentTime;
			local o = LoGetWorldObjects()
			
			udp = socket.udp()
			udp:settimeout(0)
			udp:setpeername(UDPip, UDPport)
			
			for k,v in pairs(o) do
				if v.Type.level1 == 1 then
					local unitName = v.UnitName;
					if not v.Flags["Human"] then
						unitName = "AI"
					end
					
					local coalition = SETCoalition[v.CoalitionID]
					
					local sendStr = CurrentTime .. ",S_EVENT_POSITION," .. k .. "," .. coalition .. ",AIRPLANE," .. v.Name .. "," .. unitName .. ",No Weapon,No Weapon,0," .. v.LatLongAlt.Alt .. ",POSITION," .. v.LatLongAlt.Lat .. "," .. v.LatLongAlt.Long
					
					udp:send(sendStr)
				end
			end
		
		
		end
	end
}




-- (Hook) Works just after every simulation frame.
do
	local PrevLuaExportAfterNextFrame=LuaExportAfterNextFrame;

	LuaExportAfterNextFrame=function()

		CbaconPosExpClass:Frame();

		if PrevLuaExportAfterNextFrame then
			PrevLuaExportAfterNextFrame();
		end
	end
end

end
